<?php

namespace FKSDB\Components\Forms\Controls;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ReferencedContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\SearchContainer;
use FKSDB\Components\Forms\Controls\Schedule\ExistingPaymentException;
use FKSDB\Models\ORM\IModel;
use FKSDB\Models\ORM\IService;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\Persons\IReferencedHandler;
use FKSDB\Models\Persons\ModelDataConflictException;
use FKSDB\Models\Utils\Promise;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IContainer;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Form;
use Tracy\Debugger;

/**
 * Be careful when calling getValue as it executes SQL queries and thus
 * it should always be run inside a transaction.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedId extends HiddenField {

    public const MODE_NORMAL = 'MODE_NORMAL';
    public const MODE_FORCE = 'MODE_FORCE';
    public const MODE_ROLLBACK = 'MODE_ROLLBACK';
    public const VALUE_PROMISE = '__promise';
    private const JSON_DATA = 'referencedContainer';
    private ReferencedContainer $referencedContainer;
    private SearchContainer $searchContainer;
    private IService $service;
    private IReferencedHandler $handler;
    private ?Promise $promise = null;
    private bool $modelCreated = false;
    /** @var IModel */
    private $model;
    private bool $attachedOnValidate = false;
    private bool $attachedSearch = false;

    public function __construct(SearchContainer $searchContainer, ReferencedContainer $referencedContainer, IService $service, IReferencedHandler $handler) {
        $this->referencedContainer = $referencedContainer;
        $this->getReferencedContainer()->setReferencedId($this);
        $this->searchContainer = $searchContainer;
        $this->getSearchContainer()->setReferencedId($this);

        $this->service = $service;
        $this->handler = $handler;

        parent::__construct();

        $this->monitor(Form::class, function (Form $form) {
            if (!$this->attachedOnValidate) {
                $form->onValidate[] = function () {
                    $this->createPromise();
                };
                $this->attachedOnValidate = true;
            }
        });
        $this->monitor(IContainer::class, function (IContainer $container) {
            if (!$this->attachedSearch) {
                $container->addComponent($this->getReferencedContainer(), $this->getName() . '_1');
                $container->addComponent($this->getSearchContainer(), $this->getName() . '_2');
                $this->attachedSearch = true;
            }
        });
    }

    public function getReferencedContainer(): ReferencedContainer {
        return $this->referencedContainer;
    }

    public function getSearchContainer(): SearchContainer {
        return $this->searchContainer;
    }

    protected function getPromise(): ?Promise {
        return $this->promise;
    }

    private function setPromise(Promise $promise): void {
        $this->promise = $promise;
    }

    public function getService(): IService {
        return $this->service;
    }

    public function getHandler(): IReferencedHandler {
        return $this->handler;
    }

    public function getModelCreated(): bool {
        return $this->modelCreated;
    }

    public function setModelCreated(bool $modelCreated): void {
        $this->modelCreated = $modelCreated;
    }

    /**
     * @return IModel|AbstractModelSingle
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * @param string|int|IModel|AbstractModelSingle|ModelPerson $pValue
     * @param bool $force
     * @return static
     */
    public function setValue($pValue, bool $force = false): self {
        if ($pValue instanceof IModel) {
            $personModel = $pValue;
        } elseif ($pValue === self::VALUE_PROMISE) {
            $personModel = $this->service->createNew();
        } else {
            $personModel = $this->service->findByPrimary($pValue);
        }

        if ($personModel && !$personModel->isNew()) {
            $this->model = $personModel;
        }
        $this->setModel($personModel, $force ? self::MODE_FORCE : self::MODE_NORMAL);

        if ($pValue instanceof IModel) {
            $pValue = $personModel->getPrimary();
        }
        $this->getSearchContainer()->setOption('visible', !$pValue);
        $this->getReferencedContainer()->setOption('visible', (bool)$pValue);
        return parent::setValue($pValue);
    }

    /**
     * If you are calling this method out of transaction, set $fullfilPromise to
     * false. This is the case for event form adjustments.
     *
     * @param bool $fullfilPromise
     * @return mixed
     */
    public function getValue($fullfilPromise = true) {
        if ($fullfilPromise && $this->promise) {
            return $this->promise->getValue();
        }

        $value = parent::getValue();
        return $value ?: null;
    }

    public function rollback(): void {
        if ($this->getModelCreated()) {
            $this->setModel(null, self::MODE_ROLLBACK);
            if (parent::getValue()) {
                parent::setValue(self::VALUE_PROMISE);
            }
        }
    }

    /**
     * @param bool $value
     * @return static
     */
    public function setDisabled($value = true): self {
        $this->getReferencedContainer()->setDisabled($value);
        return $this;
    }

    private function createPromise(): void {
        $values = $this->getReferencedContainer()->getValues();
        $referencedId = $this->getValue();
        $promise = new Promise(function () use ($values, $referencedId) {
            try {
                if ($referencedId === self::VALUE_PROMISE) {
                    $model = $this->handler->createFromValues($values);
                    $this->setValue($model, self::MODE_FORCE);
                    $this->setModelCreated(true);
                    return $model->getPrimary();
                } elseif ($referencedId) {
                    $model = $this->getService()->findByPrimary($referencedId);
                    $this->handler->update($model, $values);
                    // reload the model (this is workaround to avoid caching of empty but newly created referenced/related models)
                    $model = $this->getService()->findByPrimary($model->getPrimary());
                    $this->setValue($model, self::MODE_FORCE);
                    return $referencedId;
                } else {
                    $this->setValue(null, self::MODE_FORCE);
                }
            } catch (ModelDataConflictException $exception) {
                $exception->setReferencedId($this);
                throw $exception;
            } catch (ExistingPaymentException $exception) {
                $this->addError($exception->getMessage());
                $this->rollback();
            }
        });
        $referencedId = $this->getValue();
        $this->setValue($referencedId);
        $this->setPromise($promise);
    }

    public function invalidateFormGroup(): void {
        $form = $this->getForm();
        /** @var Presenter $presenter */
        $presenter = $form->lookup(Presenter::class);
        if ($presenter->isAjax()) {
            /** @var Control $control */
            $control = $form->getParent();
            $control->redrawControl(FormControl::SNIPPET_MAIN);
            $control->getTemplate()->mainContainer = $this->parent;
            $control->getTemplate()->level = 2;
            $payload = $presenter->getPayload();
            $payload->{self::JSON_DATA} = (object)[
                'id' => $this->getHtmlId(),
                'value' => $this->getValue(),
            ];
        }
    }

    protected function setModel(?IModel $model, string $mode): void {
        $this->getReferencedContainer()->setModel($model, $mode);
    }
}

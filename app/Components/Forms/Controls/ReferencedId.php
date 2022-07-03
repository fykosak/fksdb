<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ReferencedContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\SearchContainer;
use FKSDB\Components\Forms\Controls\Schedule\ExistingPaymentException;
use FKSDB\Models\Persons\ReferencedHandler;
use FKSDB\Models\Persons\ModelDataConflictException;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\Utils\Promise;
use Fykosak\NetteORM\Service;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IContainer;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Form;

/**
 * Be careful when calling getValue as it executes SQL queries and thus
 * it should always be run inside a transaction.
 */
class ReferencedId extends HiddenField
{
    public const MODE_NORMAL = 'MODE_NORMAL';
    public const MODE_FORCE = 'MODE_FORCE';
    public const MODE_ROLLBACK = 'MODE_ROLLBACK';
    public const VALUE_PROMISE = '__promise';
    private const JSON_DATA = 'referencedContainer';
    private ReferencedContainer $referencedContainer;
    private SearchContainer $searchContainer;
    private Service $service;
    private ReferencedHandler $handler;
    private ?Promise $promise = null;
    private bool $modelCreated = false;
    private ?ActiveRow $model = null;
    private bool $attachedOnValidate = false;
    private bool $attachedSearch = false;

    public function __construct(
        SearchContainer $searchContainer,
        ReferencedContainer $referencedContainer,
        Service $service,
        ReferencedHandler $handler
    ) {
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

    public function getReferencedContainer(): ReferencedContainer
    {
        return $this->referencedContainer;
    }

    public function getSearchContainer(): SearchContainer
    {
        return $this->searchContainer;
    }

    protected function getPromise(): ?Promise
    {
        return $this->promise;
    }

    private function setPromise(Promise $promise): void
    {
        $this->promise = $promise;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function getHandler(): ReferencedHandler
    {
        return $this->handler;
    }

    public function getModelCreated(): bool
    {
        return $this->modelCreated;
    }

    public function setModelCreated(bool $modelCreated): void
    {
        $this->modelCreated = $modelCreated;
    }

    public function getModel(): ?ActiveRow
    {
        return $this->model;
    }

    /**
     * @param string|int|ActiveRow|Model|ModelPerson $value
     * @return static
     */
    public function setValue($value, bool $force = false): self
    {
        if ($value instanceof ModelPerson) {
            $personModel = $value;
        } elseif ($value === self::VALUE_PROMISE) {
            $personModel = null;
        } else {
            $personModel = $this->service->findByPrimary($value);
        }

        if ($personModel) {
            $this->model = $personModel;
        }
        $this->setModel($personModel ?? null, $force ? self::MODE_FORCE : self::MODE_NORMAL);

        if ($value instanceof ModelPerson) {
            $value = $personModel->getPrimary();
        }
        $this->getSearchContainer()->setOption('visible', !$value);
        $this->getReferencedContainer()->setOption('visible', (bool)$value);
        return parent::setValue($value);
    }

    /**
     * If you are calling this method out of transaction, set $fullfilPromise to
     * false. This is the case for event form adjustments.
     *
     * @return mixed
     */
    public function getValue(bool $fullfilPromise = true)
    {
        if ($fullfilPromise && $this->promise) {
            return $this->promise->getValue();
        }
        $value = parent::getValue();
        return $value ?: null;
    }

    public function rollback(): void
    {
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
    public function setDisabled($value = true): self
    {
        $this->getReferencedContainer()->setDisabled($value);
        return $this;
    }

    private function createPromise(): void
    {
        $values = $this->getReferencedContainer()->getValues();

        $referencedId = $this->getValue();

        $promise = new Promise(function () use ($values, $referencedId) {
            try {
                if ($referencedId === self::VALUE_PROMISE) {
                    $model = $this->handler->createFromValues((array)$values);
                    $this->setValue($model, (bool)self::MODE_FORCE);
                    $this->setModelCreated(true);
                    return $model->getPrimary();
                } elseif ($referencedId) {
                    $model = $this->service->findByPrimary($referencedId);
                    $this->handler->update($model, (array)$values);
// reload the model (this is workaround to avoid caching of empty but newly created referenced/related models)
                    $model = $this->service->findByPrimary($model->getPrimary());
                    $this->setValue($model, (bool)self::MODE_FORCE);
                    return $referencedId;
                } else {
                    $this->setValue(null, (bool)self::MODE_FORCE);
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

    public function invalidateFormGroup(): void
    {
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

    protected function setModel(?ActiveRow $model, string $mode): void
    {
        $this->getReferencedContainer()->setModel($model, $mode);
    }
}

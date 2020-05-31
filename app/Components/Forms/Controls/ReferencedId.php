<?php

namespace FKSDB\Components\Forms\Controls;

use FKSDB\Components\Forms\Containers\Models\IReferencedSetter;
use FKSDB\Components\Forms\Containers\Models\ReferencedContainer;
use FKSDB\Components\Forms\Controls\Schedule\ExistingPaymentException;
use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;
use FKSDB\Utils\Promise;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Form;

/**
 * Be careful when calling getValue as it executes SQL queries and thus
 * it should always be run inside a transaction.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedId extends HiddenField {

    public const VALUE_PROMISE = '__promise';

    /**
     * @var ReferencedContainer
     */
    private $referencedContainer;

    /**
     * @var Promise
     */
    private $promise;

    private IService $service;

    private IReferencedHandler $handler;

    private IReferencedSetter $referencedSetter;

    /**
     * @var bool
     */
    private $modelCreated;

    /**
     * @var IModel
     */
    private $model;

    /**
     * ReferencedId constructor.
     * @param IService $service
     * @param IReferencedHandler $handler
     * @param IReferencedSetter $referencedSetter
     */
    public function __construct(IService $service, IReferencedHandler $handler, IReferencedSetter $referencedSetter) {
        parent::__construct();
        $this->monitor(Form::class);

        $this->service = $service;
        $this->handler = $handler;
        $this->referencedSetter = $referencedSetter;
    }

    /**
     * @return ReferencedContainer
     */
    public function getReferencedContainer() {
        return $this->referencedContainer;
    }

    public function setReferencedContainer(ReferencedContainer $referencedContainer): void {
        $this->referencedContainer = $referencedContainer;
    }

    /**
     * @return Promise
     */
    protected function getPromise() {
        return $this->promise;
    }

    /**
     * @param Promise $promise
     * @return void
     */
    private function setPromise(Promise $promise) {
        $this->promise = $promise;
    }

    /**
     * @return IService
     */
    public function getService() {
        return $this->service;
    }

    /**
     * @return IReferencedHandler
     */
    public function getHandler() {
        return $this->handler;
    }

    /**
     * @return bool
     */
    public function getModelCreated() {
        return $this->modelCreated;
    }

    /**
     * @param $modelCreated
     */
    public function setModelCreated($modelCreated) {
        $this->modelCreated = $modelCreated;
    }

    /**
     * @return IModel
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * @param $pvalue
     * @param bool $force
     * @return HiddenField|void
     */
    public function setValue($pvalue, $force = false) {
        $isPromise = ($pvalue === self::VALUE_PROMISE);
        if (!($pvalue instanceof IModel) && !$isPromise) {
            $pvalue = $this->service->findByPrimary($pvalue);
        } elseif ($isPromise) {
            $pvalue = $this->service->createNew();
        } elseif ($pvalue instanceof IModel) {
            $this->model = $pvalue;
        }
        $container = $this->referencedContainer;
        if (!$pvalue) {
            $container->setSearchButton(true);
            $container->setClearButton(false);
        } else {
            $container->setSearchButton(false);
            $container->setClearButton(true);
        }
        $this->referencedSetter->setModel($container, $pvalue, $force);

        if ($isPromise) {
            $value = self::VALUE_PROMISE;
        } elseif ($pvalue instanceof IModel) {
            $value = $pvalue->getPrimary();
        } else {
            $value = $pvalue;
        }
        parent::setValue($value);
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

    public function rollback() {
        if ($this->getModelCreated()) {
            $this->referencedSetter->setModel($this->referencedContainer, null, IReferencedSetter::MODE_ROLLBACK);
            if (parent::getValue()) {
                parent::setValue(self::VALUE_PROMISE);
            }
        }
    }

    /**
     * @param bool $value
     * @return BaseControl|void
     */
    public function setDisabled($value = true) {
        $this->referencedContainer->setDisabled($value);
    }

    /**
     * @return void
     */
    private function createPromise() {
        $referencedId = $this->getValue();
        $values = $this->referencedContainer->getValues();
        $promise = new Promise(function () use ($referencedId, $values) {
            try {
                if ($referencedId === self::VALUE_PROMISE) {

                    $model = $this->handler->createFromValues($values);
                    $this->setValue($model, IReferencedSetter::MODE_FORCE);
                    $this->setModelCreated(true);
                    return $model->getPrimary();
                } elseif ($referencedId) {
                    $model = $this->getService()->findByPrimary($referencedId);
                    $this->handler->update($model, $values);
                    // reload the model (this is workaround to avoid caching of empty but newly created referenced/related models)
                    $model = $this->getService()->findByPrimary($model->getPrimary());
                    $this->setValue($model, IReferencedSetter::MODE_FORCE);
                    return $referencedId;
                } else {
                    $this->setValue(null, IReferencedSetter::MODE_FORCE);
                }
            } catch (ModelDataConflictException $exception) {
                $exception->setReferencedId($this);
                throw $exception;
            } catch (ExistingPaymentException $exception) {
                $this->addError($exception->getMessage());
                $this->rollback();
            }
        });
        $this->setValue($referencedId);
        $this->setPromise($promise);
    }

    /** @var bool */
    private $attachedOnValidate = false;

    /**
     * @param mixed $obj
     * @return void
     */
    protected function attached($obj) {
        parent::attached($obj);
        if (!$this->attachedOnValidate && $obj instanceof Form) {
            $obj->onValidate[] = function () {
                $this->createPromise();
            };
            $this->attachedOnValidate = true;
        }
    }

}

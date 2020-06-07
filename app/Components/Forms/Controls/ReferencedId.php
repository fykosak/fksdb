<?php

namespace FKSDB\Components\Forms\Controls;

use FKSDB\Components\Forms\Containers\Models\IReferencedSetter;
use FKSDB\Components\Forms\Containers\Models\ReferencedContainer;
use FKSDB\Components\Forms\Controls\Schedule\ExistingPaymentException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\Utils\Promise;
use Nette\Forms\Controls\BaseControl;
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

    const VALUE_PROMISE = '__promise';

    /**
     * @var ReferencedContainer
     */
    private $referencedContainer;

    /**
     * @var Promise
     */
    private $promise;

    /**
     * @var IService
     */
    private $service;

    /**
     * @var IReferencedHandler
     */
    private $handler;

    /**
     * @var IReferencedSetter
     */
    private $referencedSetter;

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
        $this->service = $service;
        $this->handler = $handler;
        $this->referencedSetter = $referencedSetter;
        parent::__construct();
        $this->monitor(Form::class);
    }

    /**
     * @return ReferencedContainer
     */
    public function getReferencedContainer() {
        return $this->referencedContainer;
    }

    /**
     * @param ReferencedContainer $referencedContainer
     */
    public function setReferencedContainer(ReferencedContainer $referencedContainer) {
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
     * @param string|int|IModel|AbstractModelSingle|ModelPerson $pValue
     * @param bool $force
     * @return HiddenField
     */
    public function setValue($pValue, bool $force = false) {
        $isPromise = ($pValue === self::VALUE_PROMISE);
        if (!($pValue instanceof IModel) && !$isPromise) {
            $pValue = $this->service->findByPrimary($pValue);
        } elseif ($isPromise) {
            $pValue = $this->service->createNew();
        } elseif ($pValue instanceof IModel) {
            $this->model = $pValue;
        }
        if ($this->referencedContainer) {
            $container = $this->referencedContainer;
            if (!$pValue) {
                $container->setSearchButton(true);
                $container->setClearButton(false);
            } else {
                $container->setSearchButton(false);
                $container->setClearButton(true);
            }
            Debugger::barDump($pValue, 'setValue');
            $this->referencedSetter->setModel($container, $pValue, $force);
        }

        if ($isPromise) {
            $value = self::VALUE_PROMISE;
        } elseif ($pValue instanceof IModel) {
            $value = $pValue->getPrimary();
        } else {
            $value = $pValue;
        }
        Debugger::barDump($value);
        Debugger::barDump(debug_backtrace());
        return parent::setValue($value);
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
        $values = $this->referencedContainer->getValues();
        $promise = new Promise(function () use ($values) {
            $referencedId = $this->getValue(false);
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
        $referencedId = $this->getValue();
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

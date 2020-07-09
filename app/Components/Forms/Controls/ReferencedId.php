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

    /** @var bool */
    private $attachedOnValidate = false;

    /**
     * ReferencedId constructor.
     * @param ReferencedContainer $container
     * @param IService $service
     * @param IReferencedHandler $handler
     * @param IReferencedSetter $referencedSetter
     */
    public function __construct(ReferencedContainer $container, IService $service, IReferencedHandler $handler, IReferencedSetter $referencedSetter) {
        $this->referencedContainer = $container;
        $container->setReferencedId($this);
        $this->service = $service;
        $this->handler = $handler;
        $this->referencedSetter = $referencedSetter;
        parent::__construct();
        $this->monitor(Form::class, function (Form $form) {
            if (!$this->attachedOnValidate) {
                $form->onValidate[] = function () {
                    $this->createPromise();
                };
                $this->attachedOnValidate = true;
            }
        });
    }

    public function getReferencedContainer(): ReferencedContainer {
        return $this->referencedContainer;
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
     * @param mixed $modelCreated
     * @return void
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

        if ($pValue instanceof IModel) {
            $personModel = $this->model = $pValue;
        } elseif ($pValue === self::VALUE_PROMISE) {
            $personModel = $this->service->createNew();
        } else {
            $personModel = $this->service->findByPrimary($pValue);
        }

        if (!$personModel) {
            $this->referencedContainer->setSearchButton(true);
            $this->referencedContainer->setClearButton(false);
        } else {
            $this->referencedContainer->setSearchButton(false);
            $this->referencedContainer->setClearButton(true);
        }
        $this->referencedSetter->setModel($this->referencedContainer, $personModel, $force ? IReferencedSetter::MODE_FORCE : IReferencedSetter::MODE_NORMAL);

        if ($pValue instanceof IModel) {
            $pValue = $personModel->getPrimary();
        }
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
        $referencedId = $this->getValue();
        $promise = new Promise(function () use ($values, $referencedId) {
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
}

<?php

namespace FKS\Components\Forms\Controls;

use FKS\Components\Forms\Containers\IReferencedSetter;
use FKS\Components\Forms\Containers\ReferencedContainer;
use FKS\Components\Forms\Controls\ModelDataConflictException;
use FKS\Utils\Promise;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Form;
use ORM\IModel;
use ORM\IService;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
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
     * @var boolean
     */
    private $modelCreated;

    function __construct(IService $service, IReferencedHandler $handler, IReferencedSetter $referencedSetter) {
        parent::__construct();
        $this->monitor('Nette\Forms\Form');

        $this->service = $service;
        $this->handler = $handler;
        $this->referencedSetter = $referencedSetter;
    }

    public function getReferencedContainer() {
        return $this->referencedContainer;
    }

    public function setReferencedContainer(ReferencedContainer $referencedContainer) {
        $this->referencedContainer = $referencedContainer;
    }

    protected function getPromise() {
        return $this->promise;
    }

    private function setPromise(Promise $promise) {
        $this->promise = $promise;
    }

    public function getService() {
        return $this->service;
    }

    public function getHandler() {
        return $this->handler;
    }

    public function getModelCreated() {
        return $this->modelCreated;
    }

    public function setModelCreated($modelCreated) {
        $this->modelCreated = $modelCreated;
    }

    public function setValue($pvalue, $force = false) {
        $isPromise = ($pvalue === self::VALUE_PROMISE);
        if (!($pvalue instanceof IModel) && !$isPromise) {
            $pvalue = $this->service->findByPrimary($pvalue);
        } else if ($isPromise) {
            $pvalue = $this->service->createNew();
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
        } else if ($pvalue instanceof IModel) {
            $value = $pvalue->getPrimary();
        } else {
            $value = $pvalue;
        }
        parent::setValue($value);
    }

    public function getValue($fullfilPromise = true) {
        if ($fullfilPromise && $this->promise) {
            return $this->promise->getValue();
        }

        $value = parent::getValue();
        return $value ? : null;
    }

    public function rollback() {
        if ($this->getModelCreated()) {
            $this->referencedSetter->setModel($this->referencedContainer, NULL, IReferencedSetter::MODE_ROLLBACK);
            if (parent::getValue()) {
                parent::setValue(self::VALUE_PROMISE);
            }
        }
    }

    public function setDisabled($value = TRUE) {
        $this->referencedContainer->setDisabled($value);
    }

    private function createPromise() {
        $referencedId = $this->getValue();

        $referencedIdControl = $this;
        $values = $this->referencedContainer->getValues();
        $handler = $this->handler;
        $promise = new Promise(function() use($handler, $referencedId, $values, $referencedIdControl) {
                    try {
                        if ($referencedId === self::VALUE_PROMISE) {
                            $model = $handler->createFromValues($values);
                            $referencedIdControl->setValue($model, IReferencedSetter::MODE_FORCE);
                            $referencedIdControl->setModelCreated(true);
                            return $model->getPrimary();
                        } else if ($referencedId) {
                            $model = $referencedIdControl->getService()->findByPrimary($referencedId);
                            $handler->update($model, $values);
                            // reload the model (this is workaround to avoid caching of empty but newly created referenced/related models)
                            $model = $referencedIdControl->getService()->findByPrimary($model->getPrimary());
                            $referencedIdControl->setValue($model, IReferencedSetter::MODE_FORCE);
                            return $referencedId;
                        } else {
                            $referencedIdControl->setValue(null, IReferencedSetter::MODE_FORCE);
                        }
                    } catch (ModelDataConflictException $e) {
                        $e->setReferencedId($referencedIdControl);
                        throw $e;
                    }
                });
        $this->setValue($referencedId);
        $this->setPromise($promise);
    }

    private $attachedOnValidate = false;

    protected function attached($obj) {
        parent::attached($obj);
        if (!$this->attachedOnValidate && $obj instanceof Form) {
            $that = $this;
            $obj->onValidate[] = function(Form $form) use($that) {
                        $that->createPromise();
                    };
            $this->attachedOnValidate = true;
        }
    }

}

<?php

namespace FKS\Components\Forms\Controls;

use FKS\Components\Forms\Containers\IReferencedSetter;
use FKS\Components\Forms\Containers\ReferencedContainer;
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

    public function setValue($pvalue) {
        $isPromise = ($pvalue === self::VALUE_PROMISE);
        if (!($pvalue instanceof IModel) && !$isPromise) {
            $pvalue = $this->service->findByPrimary($pvalue);
        }
        $container = $this->referencedContainer;
        if ($isPromise) {
            $container->setSearchButton(false);
            $container->setClearButton(true);
        } else if (!$pvalue) {
            $container->setSearchButton(true);
            $container->setClearButton(false);
        } else {
            $container->setSearchButton(false);
            $container->setClearButton(true);

            $this->referencedSetter->setModel($container, $pvalue);
        }
        if ($isPromise) {
            $value = self::VALUE_PROMISE;
        } else if ($pvalue instanceof IModel) {
            $value = $pvalue->getPrimary();
        } else {
            $value = $pvalue;
        }
        parent::setValue($value);
    }

    public function getValue() {
        if ($this->promise) {
            return $this->promise->getValue();
        }

        return parent::getValue();
    }

    public function setDisabled($value = TRUE) {
        $this->referencedContainer->setDisabled($value);
    }

    private function createPromise() {
        $referencedId = $this->getValue();
        if (!$referencedId) {
            return;
        }

        $referencedIdControl = $this;
        $values = $this->referencedContainer->getValues();
        $handler = $this->handler;
        $promise = new Promise(function() use($handler, $referencedId, $values, $referencedIdControl) {
                    if ($referencedId === self::VALUE_PROMISE) {
                        try {
                            $model = $handler->createFromValues($values);
                            return $model;
                        } catch (AlreadyExistsException $e) {
                            $e->setIdName($referencedIdControl->getName());
                            $referencedIdControl->setValue($e->getModel());
                            throw $e;
                        }
                    } else if ($referencedId) {
                        $model = $referencedIdControl->getService()->findByPrimary($referencedId);
                        $handler->update($model, $values);
                        return $referencedId;
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

<?php

namespace Events\Model;

use Events\Machine\BaseMachine;
use Nette\Forms\Container;
use Nette\FreezableObject;
use ORM\IModel;
use ORM\IService;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class BaseHolder extends FreezableObject {

    const STATE_COLUMN = 'status';

    /**
     * @var IService
     */
    private $service;

    /**
     * @var IModel 
     */
    private $model;

    /**
     *
     * @var Field[]
     */
    private $fields = array();

    public function addField(Field $field) {
        $this->updating();
        $field->setBaseHolder($this);
        $field->freeze();

        $name = $field->getName();
        $this->fields[$name] = $field;
    }

    public function getModel() {
        if (!$this->model) {
            $this->model = $this->getService()->createNew();
        }
        return $this->model;
    }

    /**
     * @param int|IModel $model
     */
    public function setModel($model) {
        if ($model instanceof IModel) {
            $this->model = $model;
        } else {
            $this->model = $this->service->findByPrimary($model);
        }
    }

    public function saveModel() {
        if ($this->getModelState() == BaseMachine::STATE_TERMINATED) {
            $this->service->dispose($this->getModel());
        } else {
            $this->service->saev($this->getModel());
        }
    }

    /**
     * @return string
     */
    public function getModelState() {
        $model = $this->getModel();
        if ($model->isNew() && !$model[self::STATE_COLUMN]) {
            return BaseMachine::STATE_INIT;
        } else {
            return $model[self::STATE_COLUMN];
        }
    }

    public function setModelState($state) {
        $model = $this->getModel();
        $model[self::STATE_COLUMN] = $state;
    }

    public function updateModel($values) {
        $this->getService()->updateModel($this->getModel(), $values);
    }

    public function getService() {
        return $this->service;
    }

    public function setService(IService $service) {
        $this->updating();
        $this->service = $service;
    }

    /**
     * @return Container
     */
    public function createFormContainer(BaseMachine $machine) {
        $container = new Container();

        foreach ($this->fields as $name => $field) {
            if (!$field->isVisible($machine)) {
                continue;
            }
            $component = $field->createFormComponent($machine);
            $container->addComponent($component, $name);
        }

        return $container;
    }

}

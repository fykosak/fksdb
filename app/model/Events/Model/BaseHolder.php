<?php

namespace Events\Model;

use Events\Machine\Machine;
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
        $name = $field->getName();
        $this->fields[$name] = $field;
        $field->setBaseHolder($this);
        $field->freeze();
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

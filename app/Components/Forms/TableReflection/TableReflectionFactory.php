<?php

namespace FKSDB\Components\Forms\TableReflection;

use FKSDB\Components\Forms\Containers\ModelContainer;
use Nette\Forms\Controls\BaseControl;

abstract class TableReflectionFactory {

    abstract public function createField(string $fieldName, array $data = []): BaseControl;

    public function createContainer(array $fields) {
        $container = new ModelContainer();
        $this->appendFields($container, $fields);
        return $container;
    }

    public function appendFields(ModelContainer $container, $fields) {
        foreach ($fields as $fieldName => $metadata) {
            $this->appendField($container, $fieldName, $metadata);
        }
    }

    public function appendField(ModelContainer $container, $fieldName, $metadata) {
        $component = $this->createField($fieldName, $metadata);
        $container->addComponent($component, $fieldName);
    }
}

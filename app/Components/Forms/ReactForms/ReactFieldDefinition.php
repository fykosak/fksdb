<?php

use FKS\Components\Forms\Containers\IWriteonly;

trait ReactFieldDefinition {

    public function createReactDefinition(): \ReactField {
        $secure = $this instanceof IWriteonly;
        $def = new \ReactField($secure, $this->getLabel(), $this->getOption('description'));
        $def->addRules($this->getRules());
        return $def;
    }
}

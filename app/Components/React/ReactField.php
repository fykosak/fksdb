<?php

namespace FKSDB\Components\React;

trait ReactField {
    protected function appendProperty() {
        $this->setAttribute('data-react-root', true);
        $this->setAttribute('data-module', $this->getModuleName());
        $this->setAttribute('data-component', $this->getComponentName());
        $this->setAttribute('data-mode', $this->getMode());
        $this->setAttribute('data-data', $this->getData());
    }
}

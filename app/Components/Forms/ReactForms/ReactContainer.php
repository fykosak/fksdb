<?php

class ReactContainer implements IReactComponent {
    private $label;
    private $children = [];

    /**
     * @param mixed $label
     */
    public function setLabel($label) {
        $this->label = $label;
    }

    /**
     * @param array $children
     */
    public function setChildren(array $children) {
        $this->children = $children;
    }

    public function __toArray() {
        return [
            'type' => 'container',
            'label' => $this->label,
            'children' => $this->children
        ];
    }
}

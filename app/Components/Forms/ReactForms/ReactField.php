<?php

class ReactField implements IReactComponent {
    /**
     * @var boolean
     */
    private $secure;
    /**
     * @var boolean
     */
    private $readonly;
    /**
     * @var boolean
     */
    private $disabled;
    /**
     * @var bool
     */
    private $filled = false;
    private $label = null;
    private $description = null;
    private $value = null;
    private $required = false;
    private $rules = [];
    private $data = null;

    public function __construct($secure = false, $label = null, $description = null) {
        $this->setSecure($secure);
        $this->setLabel($label);
        $this->setDescription($description);
    }

    /**
     * @param bool $secure
     */
    public function setSecure(bool $secure) {
        $this->secure = $secure;
    }

    /**
     * @param bool $readonly
     */
    public function setReadonly(bool $readonly) {
        $this->readonly = $readonly;
    }

    /**
     * @param bool $disabled
     */
    public function setDisabled(bool $disabled) {
        $this->disabled = $disabled;
    }

    /**
     * @param null $label
     */
    public function setLabel($label) {
        $this->label = $label;
    }

    /**
     * @param null $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @param null $value
     */
    public function setValue($value) {
        $this->value = $value;
    }


    public function setFilled($filled) {
        $this->filled = $filled;
    }

    public function setRequired($required) {
        $this->required = $required;
    }

    public function setRules($rules = []) {
        $this->rules = $rules;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function __toArray() {
        return [
            'type' => 'field',
            'secure' => $this->secure,
            'readonly' => $this->readonly,
            'disabled' => $this->disabled,
        ];
    }
}

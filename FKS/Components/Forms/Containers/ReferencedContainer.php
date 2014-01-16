<?php

namespace FKS\Components\Forms\Containers;

use FKS\Components\Forms\Controls\ReferencedId;
use Nette\ArrayHash;
use Nette\Callback;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\IControl;
use Nette\Utils\Arrays;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedContainer extends Container {

    const SEARCH_NONE = 'none';
    const CONTROL_SEARCH = '_c_search';
    const SUBMIT_SEARCH = '__search';
    const SUBMIT_CLEAR = '__clear';
    const FILLED_HIDDEN = 'hidden';
    const FILLED_DISABLED = 'disabled';
    const FILLED_MODIFIABLE = 'modifiable';

    /**
     * @var Component[]
     */
    private $hiddenComponents = array();

    /**
     * @var ReferencedId
     */
    private $referencedId;

    /**
     * @var boolean
     */
    private $hasSearch;

    /**
     * @var boolean
     */
    private $allowClear = true;

    /**
     * @var enum
     */
    private $fillingMode = self::FILLED_DISABLED;

    /**
     * @var Callback
     */
    private $searchCallback;

    /**
     * @var Callback
     */
    private $termToValuesCallback;

    /**
     * Custom metadata.
     * 
     * @var mixed
     */
    private $metadata;

    function __construct(ReferencedId $referencedId) {
        parent::__construct();

        $this->referencedId = $referencedId;

        $this->createClearButton();
        $this->createSearchButton();
        $this->referencedId->setReferencedContainer($this);
    }

    public function setDisabled($value = TRUE) {
        foreach ($this->getControls() as $control) {
            $control->setDisabled($value);
        }
    }

    public function setSearch(IControl $control = null, $searchCallback = null, $termToValuesCallback = null) {
        if ($control == null) {
            $this->referencedId->setValue(null); //is it needed?
            $this->hasSearch = false;
        } else {
            $this->searchCallback = new Callback($searchCallback);
            $this->termToValuesCallback = new Callback($termToValuesCallback);
            $this->addComponent($control, self::CONTROL_SEARCH);
            $this->hasSearch = true;
        }
    }

    public function getAllowClear() {
        return $this->allowClear;
    }

    public function setAllowClear($allowClear) {
        $this->allowClear = $allowClear;
    }

    public function getFillingMode() {
        return $this->fillingMode;
    }

    public function setFillingMode($fillingMode) {
        $this->fillingMode = $fillingMode;
    }

    public function getMetadata() {
        return $this->metadata;
    }

    public function setMetadata($metadata) {
        $this->metadata = $metadata;
    }

    /**
     * Swaps hidden and attached components from/to the container.
     * 
     * @staticvar array $searchComponents
     * @param boolean $value
     */
    public function setSearchButton($value) {
        static $searchComponents = array(
    self::CONTROL_SEARCH,
    self::SUBMIT_SEARCH,
        );

        $value = $value && $this->hasSearch;

        foreach ($this->hiddenComponents as $name => $component) {
            if ($value == !!Arrays::grep($searchComponents, "/^$name/")) {
                $this->addComponent($component, $name);
                unset($this->hiddenComponents[$name]);
            }
        }


        foreach ($this->getComponents() as $name => $component) {
            if ($value == !Arrays::grep($searchComponents, "/^$name/")) {
                $this->removeComponent($component);
                $this->hiddenComponents[$name] = $component;
            }
        }
    }

    /**
     * Toggles button used for clearing the element.
     * 
     * @param boolean $value
     */
    public function setClearButton($value) {
        if (!$this->getAllowClear()) {
            $value = false;
        }
        if ($value) {
            $component = Arrays::get($this->hiddenComponents, self::SUBMIT_CLEAR, null);
            if ($component) {
                $this->addComponent($component, self::SUBMIT_CLEAR);
                unset($this->hiddenComponents[self::SUBMIT_CLEAR]);
            }
        } else {
            $component = $this->getComponent(self::SUBMIT_CLEAR, false);
            if ($component) {
                $this->hiddenComponents[self::SUBMIT_CLEAR] = $component;
                $this->removeComponent($component);
            }
        }
    }

    private function createClearButton() {
        $that = $this;
        $this->addSubmit(self::SUBMIT_CLEAR, 'X')
                        ->setValidationScope(false)
                ->onClick[] = function(SubmitButton $submit) use($that) {
                    $that->referencedId->setValue(null);
                };
    }

    private function createSearchButton() {
        $that = $this;
        $this->addSubmit(self::SUBMIT_SEARCH, _('Najít'))
                        ->setValidationScope(false)
                ->onClick[] = function(SubmitButton $submit) use($that) {
                    $term = $that->getComponent(self::CONTROL_SEARCH)->getValue();
                    $model = $that->searchCallback->invoke($term);

                    $values = new ArrayHash();
                    if (!$model) {
                        $model = ReferencedId::VALUE_PROMISE;
                        $values = $that->termToValuesCallback->invoke($term);
                    }
                    $that->referencedId->setValue($model);
                    $that->setValues($values);
                };
    }

}


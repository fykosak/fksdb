<?php

namespace FKS\Components\Forms\Containers;

use FKS\Application\IJavaScriptCollector;
use FKS\Components\Forms\Controls\ReferencedId;
use Nette\ArrayHash;
use Nette\Callback;
use Nette\ComponentModel\Component;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Utils\Arrays;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedContainer extends ContainerWithOptions {

    const ID_MASK = 'frm%s-%s';
    const CSS_AJAX = 'ajax';
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
     * TODO refactor to options?
     * 
     * @var mixed
     */
    private $metadata;

    function __construct(ReferencedId $referencedId) {
        parent::__construct();
        $this->monitor('FKS\Application\IJavaScriptCollector');
        $this->monitor('Nette\Forms\Form');

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
        $submit = $this->addSubmit(self::SUBMIT_SEARCH, _('Najít'))
                ->setValidationScope(false);
        $submit->getControlPrototype()->class[] = self::CSS_AJAX;
        $submit->onClick[] = function(SubmitButton $submit) use($that) {
                    $form = $that->getForm();
                    $presenter = $form->lookup('Nette\Application\UI\Presenter');
                    //$template = $form->getRenderer()->getTemplate();
                    if ($presenter->isAjax()) {
                        //NOTE: stejně se renderuje všechno...
                        $control = $form->getParent();
                        $control->invalidateControl();
                        $control->invalidateControl('form');
                        $control->invalidateControl($that->getHtmlId());
                        $control->invalidateControl('groupsContainer');
//                        $presenter->invalidateControl('jsLoader');
//                        $presenter->invalidateControl(null);
//                        $presenter->invalidateControl($that->getHtmlId());
//                        $presenter->invalidateControl('groupsContainer');
                    }

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

    private function getHtmlId() {
        if (!$this->getOption('id')) {
            $this->setOption('id', sprintf(self::ID_MASK, $this->getForm()->getName(), $this->lookupPath('Nette\Forms\Form')));
        }
        return $this->getOption('id');
    }

    private $attachedJS = false;
    private $attachedAjax = false;

    protected function attached($obj) {
        parent::attached($obj);
        if (!$this->attachedJS && $obj instanceof IJavaScriptCollector) {
            $this->attachedJS = true;
            $obj->registerJSFile('js/referencedContainer.js');
            $obj->registerJSCode($this->getJSCode(), $this->getHtmlId());
        }
        if (!$this->attachedAjax && $obj instanceof Form) {
            $this->attachedAjax = true;
            $this->getForm()->getElementPrototype()->class[] = self::CSS_AJAX;
        }
    }

    protected function detached($obj) {
        parent::detached($obj);
        if ($obj instanceof IJavaScriptCollector) {
            $this->attachedJS = false;
            $obj->unregisterJSCode($this->getHtmlId());
        }
    }

    private function getJSCode() {
        $id = $this->getHtmlId();
        $referencedId = $this->referencedId->getHtmlId();
        $code = "jQuery(function() { var el = jQuery('#$id').referencedContainer({ refId: jQuery('#$referencedId')});";
        //TODO
//        if ($this->renderMethod) {
//            $code .= "el.data('autocomplete').data('ui-autocomplete')._renderItem = function(ul, item) { {$this->renderMethod} };";
//        }
        $code .= "});";

        return $code;
    }

}


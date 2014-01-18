<?php

namespace FKS\Components\Forms\Containers;

use FKS\Application\IJavaScriptCollector;
use FKS\Components\Controls\FormControl;
use FKS\Components\Forms\Controls\ReferencedId;
use Nette\ArrayHash;
use Nette\Callback;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\InvalidStateException;
use Nette\Utils\Arrays;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedContainer extends ContainerWithOptions {

    const ID_MASK = 'frm%s-%s';
    const CSS_AJAX = 'ajax';
    const JSON_DATA = 'referencedContainer';
    const SEARCH_NONE = 'none';
    const CONTROL_SEARCH = '_c_search';
    const CONTROL_COMPACT = '_c_compact';
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
        $this->createCompactValue();
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

    protected function validateChildComponent(IComponent $child) {
        if (!$child instanceof BaseControl && !$child instanceof ContainerWithOptions) {
            throw new InvalidStateException(__CLASS__ . ' can contain only components with get/set option funcionality, ' . get_class($child) . ' given.');
        }
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
                $component->setOption('visible', true);
                unset($this->hiddenComponents[$name]);
            }
        }


        foreach ($this->getComponents() as $name => $component) {
            if ($value == !Arrays::grep($searchComponents, "/^$name/")) {
                $component->setOption('visible', false);
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
                $component->setOption('visible', true);
                unset($this->hiddenComponents[self::SUBMIT_CLEAR]);
            }
        } else {
            $component = $this->getComponent(self::SUBMIT_CLEAR, false);
            if ($component) {
                $this->hiddenComponents[self::SUBMIT_CLEAR] = $component;
                $component->setOption('visible', false);
            }
        }
    }

    private function createClearButton() {
        $that = $this;
        $submit = $this->addSubmit(self::SUBMIT_CLEAR, 'X')
                ->setValidationScope(false);
        $submit->getControlPrototype()->class[] = self::CSS_AJAX;
        $submit->onClick[] = function(SubmitButton $submit) use($that) {
                    $that->referencedId->setValue(null);
                    $that->invalidateFormGroup();
                };
    }

    private function createSearchButton() {
        $that = $this;
        $submit = $this->addSubmit(self::SUBMIT_SEARCH, _('Najít'))
                ->setValidationScope(false);
        $submit->getControlPrototype()->class[] = self::CSS_AJAX;
        $submit->onClick[] = function(SubmitButton $submit) use($that) {
                    $term = $that->getComponent(self::CONTROL_SEARCH)->getValue();
                    $model = $that->searchCallback->invoke($term);

                    $values = new ArrayHash();
                    if (!$model) {
                        $model = ReferencedId::VALUE_PROMISE;
                        $values = $that->termToValuesCallback->invoke($term);
                    }
                    $that->referencedId->setValue($model);
                    $that->setValues($values);
                    $that->invalidateFormGroup();
                };
    }

    private function createCompactValue() {
        $this->addHidden(self::CONTROL_COMPACT);
    }

    private function invalidateFormGroup() {
        $form = $this->getForm();
        $presenter = $form->lookup('Nette\Application\UI\Presenter');
        if ($presenter->isAjax()) {
            $control = $form->getParent();
            $control->invalidateControl(FormControl::SNIPPET_MAIN);
            $control->getTemplate()->mainContainer = $this;
            $control->getTemplate()->level = 2; //TODO should depend on lookup path
            $payload = $presenter->getPayload();
            $payload->{self::JSON_DATA} = (object) array(
                        'id' => $this->referencedId->getHtmlId(),
                        'value' => $this->referencedId->getValue(),
            );
        }
    }

    private $attachedJS = false;
    private $attachedAjax = false;

    protected function attached($obj) {
        parent::attached($obj);
        if (!$this->attachedJS && $obj instanceof IJavaScriptCollector) {
            $this->attachedJS = true;
            $obj->registerJSFile('js/referencedContainer.js');
            $this->updateHtmlData();
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
            $obj->unregisterJSFile('js/referencedContainer.js');
        }
    }

    /**
     * @note Must be called after a form is attached.
     */
    private function updateHtmlData() {
        $this->setOption('id', sprintf(self::ID_MASK, $this->getForm()->getName(), $this->lookupPath('Nette\Forms\Form')));
        $referencedId = $this->referencedId->getHtmlId();
        $this->setOption('data', array(
            'referenced-id' => $referencedId,
            'referenced' => (int) true,
        ));
    }

}


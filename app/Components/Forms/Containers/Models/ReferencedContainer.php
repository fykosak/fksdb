<?php

namespace FKSDB\Components\Forms\Containers\Models;

use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\ReferencedId;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\InvalidStateException;
use Nette\Utils\ArrayHash;
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
    const CONTROL_SEARCH = '_c_search';
    const CONTROL_COMPACT = '_c_compact';
    const SUBMIT_SEARCH = '__search';
    const SUBMIT_CLEAR = '__clear';

    /**
     * @var Component[]
     */
    private $hiddenComponents = [];

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
     * @var callable
     */
    private $searchCallback;

    /**
     * @var callable
     */
    private $termToValuesCallback;

    /**
     * ReferencedContainer constructor.
     * @param ReferencedId $referencedId
     */
    function __construct(ReferencedId $referencedId) {
        parent::__construct();
        $this->monitor(IJavaScriptCollector::class);
        $this->monitor(Form::class);

        $this->referencedId = $referencedId;

        $this->createClearButton();

        $this->createCompactValue();
        $this->referencedId->setReferencedContainer($this);
    }

    /**
     * @return ReferencedId
     */
    public function getReferencedId() {
        return $this->referencedId;
    }

    /**
     * @param bool $value
     */
    public function setDisabled($value = TRUE) {
        foreach ($this->getControls() as $control) {
            $control->setDisabled($value);
        }
    }

    /**
     * @param IControl|null $control
     * @param callable|null $searchCallback
     * @param callable|null $termToValuesCallback
     * @throws \Nette\Utils\RegexpException
     */
    public function setSearch(IControl $control = null, callable $searchCallback = null, callable $termToValuesCallback = null) {
        if ($control == null) {
            $this->referencedId->setValue(null); //is it needed?
            $this->hasSearch = false;
        } else {
            $this->searchCallback = $searchCallback;
            $this->termToValuesCallback = $termToValuesCallback;
            $this->addComponent($control, self::CONTROL_SEARCH);
            $this->hasSearch = true;
        }
        $this->createSearchButton();
    }

    /**
     * @return bool
     */
    public function getAllowClear() {
        return $this->allowClear;
    }

    /**
     * @param $allowClear
     */
    public function setAllowClear($allowClear) {
        $this->allowClear = $allowClear;
    }

    /**
     * @param IComponent $child
     */
    protected function validateChildComponent(IComponent $child) {
        if (!$child instanceof BaseControl && !$child instanceof ContainerWithOptions) {
            throw new InvalidStateException(__CLASS__ . ' can contain only components with get/set option funcionality, ' . get_class($child) . ' given.');
        }
    }

    /**
     * @return bool
     */
    public function isSearchSubmitted() {
        return $this->getForm(false) && $this->getComponent(self::SUBMIT_SEARCH)->isSubmittedBy();
    }

    /**
     * @param ArrayHash $conflicts
     * @param null $container
     */
    public function setConflicts(ArrayHash $conflicts, $container = null) {
        $container = $container ?: $this;
        foreach ($conflicts as $key => $value) {
            $component = $container->getComponent($key, false);
            if ($component instanceof Container) {
                $this->setConflicts($value, $component);
            } else if ($component instanceof BaseControl) {
                $component->addError(null);
            }
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
                $this->showComponent($name, $component);
            }
        }


        foreach ($this->getComponents() as $name => $component) {
            if ($value == !Arrays::grep($searchComponents, "/^$name/")) {
                $this->hideComponent($name, $component);
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
                $this->showComponent(self::SUBMIT_CLEAR, $component);
            }
        } else {
            $component = $this->getComponent(self::SUBMIT_CLEAR, false);
            if ($component) {
                $this->hideComponent(self::SUBMIT_CLEAR, $component);
            }
        }
    }

    private function createClearButton() {
        $submit = $this->addSubmit(self::SUBMIT_CLEAR, 'X')
            ->setValidationScope(false);
        $submit->getControlPrototype()->class[] = self::CSS_AJAX;
        $submit->onClick[] = function () {
            $this->referencedId->setValue(null);
            $this->invalidateFormGroup();
        };
    }

    private function createSearchButton() {
        $submit = $this->addSubmit(self::SUBMIT_SEARCH, _('Najít'));
        $submit->setValidationScope(false);

        $submit->getControlPrototype()->class[] = self::CSS_AJAX;

        $submit->onClick[] = function () {

            $term = $this->getComponent(self::CONTROL_SEARCH)->getValue();
            $model = ($this->searchCallback)($term);

            $values = new ArrayHash();
            if (!$model) {
                $model = ReferencedId::VALUE_PROMISE;
                $values = ($this->termToValuesCallback)($term);
            }
            $this->referencedId->setValue($model);
            $this->setValues($values);
            $this->invalidateFormGroup();
        };
    }

    private function createCompactValue() {
        $this->addHidden(self::CONTROL_COMPACT);
    }

    private function invalidateFormGroup() {
        $form = $this->getForm();
        $presenter = $form->lookup(Presenter::class);
        if ($presenter->isAjax()) {
            $control = $form->getParent();
            $control->invalidateControl(FormControl::SNIPPET_MAIN);
            $control->getTemplate()->mainContainer = $this;
            $control->getTemplate()->level = 2; //TODO should depend on lookup path
            $payload = $presenter->getPayload();
            $payload->{self::JSON_DATA} = (object)array(
                'id' => $this->referencedId->getHtmlId(),
                'value' => $this->referencedId->getValue(),
            );
        }
    }

    private $attachedJS = false;
    private $attachedAjax = false;

    /**
     * @param $obj
     */
    protected function attached($obj) {
        parent::attached($obj);
        if (!$this->attachedJS && $obj instanceof IJavaScriptCollector) {
            $this->attachedJS = true;
            $obj->registerJSFile('js/referencedContainer.js');
            $this->updateHtmlData();
        }
        if (!$this->attachedAjax && $obj instanceof Form) {
            $this->attachedAjax = true;
            //  $this->getForm()->getElementPrototype()->class[] = self::CSS_AJAX;
        }
    }

    /**
     * @param $obj
     */
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
        $this->setOption('data-referenced-id', $referencedId);
        $this->setOption('data-referenced', 1);
    }

    /**
     * @param $name
     * @param ContainerWithOptions $component
     */
    private function hideComponent($name, $component) {
        $component->setOption('visible', false);
        if ($name) {
            $this->hiddenComponents[$name] = $component;
        }
        if ($component instanceof BaseControl) {
            //$component->setOption('wasDisabled', $component->isDisabled());
            $component->setDisabled(true);
        } else if ($component instanceof Container) {
            foreach ($component->getComponents() as $subcomponent) {
                $this->hideComponent(null, $subcomponent);
            }
        }
    }

    /**
     * @param $name
     * @param ContainerWithOptions $component
     */
    private function showComponent($name, $component) {
        $component->setOption('visible', true);
        if ($name) {
            unset($this->hiddenComponents[$name]);
        }
        if ($component instanceof BaseControl) {
            //$component->setDisabled($component->getOption('wasDisabled', $component->isDisabled()));
            $component->setDisabled(false);
        } else if ($component instanceof Container) {
            foreach ($component->getComponents() as $subcomponent) {
                $this->showComponent(null, $subcomponent);
            }
        }
    }

}

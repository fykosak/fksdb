<?php

namespace FKSDB\Components\Forms\Containers\Models;

use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\ReferencedId;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\InvalidStateException;
use Nette\Utils\ArrayHash;
use Persons\IModifiabilityResolver;
use Persons\IVisibilityResolver;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedContainer extends ContainerWithOptions {

    const ID_MASK = 'frm%s-%s';
    const CSS_AJAX = 'ajax';
    const JSON_DATA = 'referencedContainer';
    const CONTROL_COMPACT = '_c_compact';
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
     * @var bool
     */
    private $allowClear = true;
    /**
     * @var bool
     */
    private $attachedJS = false;
    /** @var IModifiabilityResolver */
    public $modifiabilityResolver;
    /** @var IVisibilityResolver */
    public $visibilityResolver;

    /**
     * ReferencedContainer constructor.
     * @param ReferencedId $referencedId
     */
    public function __construct(ReferencedId $referencedId) {
        parent::__construct();
        $this->monitor(IJavaScriptCollector::class, function (IJavaScriptCollector $collector) {
            if (!$this->attachedJS) {
                $this->attachedJS = true;
                $collector->registerJSFile('js/referencedContainer.js');
                $this->updateHtmlData();
            }
        }, function (IJavaScriptCollector $collector) {
            $this->attachedJS = false;
            $collector->unregisterJSFile('js/referencedContainer.js');
        });
        $this->referencedId = $referencedId;
        $this->createClearButton();
        $this->createCompactValue();
        $this->referencedId->setReferencedContainer($this);
    }

    public function getReferencedId(): ReferencedId {
        return $this->referencedId;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setDisabled(bool $value = true) {
        /** @var BaseControl $control */
        foreach ($this->getControls() as $control) {
            $control->setDisabled($value);
        }
    }

    public function getAllowClear(): bool {
        return $this->allowClear;
    }

    /**
     * @param bool $allowClear
     * @return void
     */
    public function setAllowClear(bool $allowClear) {
        $this->allowClear = $allowClear;
    }

    /**
     * @param IComponent $child
     * @return void
     */
    protected function validateChildComponent(IComponent $child) {
        if (!$child instanceof BaseControl && !$child instanceof ContainerWithOptions) {
            throw new InvalidStateException(__CLASS__ . ' can contain only components with get/set option funcionality, ' . get_class($child) . ' given.');
        }
    }

    /**
     * @param array|ArrayHash $conflicts
     * @param null $container
     */
    public function setConflicts($conflicts, $container = null) {
        $container = $container ?: $this;
        foreach ($conflicts as $key => $value) {
            $component = $container->getComponent($key, false);
            if ($component instanceof Container) {
                $this->setConflicts($value, $component);
            } elseif ($component instanceof BaseControl) {
                $component->addError(null);
            }
        }
    }

    /**
     * Toggles button used for clearing the element.
     *
     * @param bool $value
     */
    public function setClearButton($value) {
        if (!$this->getAllowClear()) {
            $value = false;
        }
        if ($value) {
            $component = $this->hiddenComponents[self::SUBMIT_CLEAR] ?? null;
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

    private function createCompactValue() {
        $this->addHidden(self::CONTROL_COMPACT);
    }

    private function invalidateFormGroup() {
        $form = $this->getForm();
        /** @var Presenter $presenter */
        $presenter = $form->lookup(Presenter::class);
        if ($presenter->isAjax()) {
            /** @var Control $control */
            $control = $form->getParent();
            $control->redrawControl(FormControl::SNIPPET_MAIN);
            $control->getTemplate()->mainContainer = $this;
            $control->getTemplate()->level = 2; //TODO should depend on lookup path
            $payload = $presenter->getPayload();
            $payload->{self::JSON_DATA} = (object)[
                'id' => $this->referencedId->getHtmlId(),
                'value' => $this->referencedId->getValue(),
            ];
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
     * @param string $name
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
        } elseif ($component instanceof Container) {
            foreach ($component->getComponents() as $subComponent) {
                $this->hideComponent(null, $subComponent);
            }
        }
    }

    /**
     * @param string $name
     * @param ContainerWithOptions|IComponent $component
     */
    private function showComponent($name, $component) {
        $component->setOption('visible', true);
        if ($name) {
            unset($this->hiddenComponents[$name]);
        }
        if ($component instanceof BaseControl) {
            //$component->setDisabled($component->getOption('wasDisabled', $component->isDisabled()));
            $component->setDisabled(false);
        } elseif ($component instanceof Container) {
            foreach ($component->getComponents() as $subComponent) {
                $this->showComponent(null, $subComponent);
            }
        }
    }

}

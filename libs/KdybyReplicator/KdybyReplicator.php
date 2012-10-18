<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * Modified for PHP 5.2 by Michal Koutný <xm.koutny@gmail.com>.
 */

/**
 * @author Filip Procházka <filip@prochazka.su>
 * @author Jan Tvrdík
 *
 * @method NForm getForm()
 */
class KdybyReplicator extends NFormContainer {

    /** @var bool */
    public $forceDefault;

    /** @var int */
    public $createDefault;

    /** @var string */
    public $containerClass = 'NFormContainer';

    /** @var callable */
    protected $factoryCallback;

    /** @var boolean */
    private $submittedBy = FALSE;

    /** @var array */
    private $created = array();

    /** @var IHttpRequest */
    private $httpRequest;

    /** @var array */
    private $httpPost;

    /**
     * @param callable $factory
     * @param int $createDefault
     * @param bool $forceDefault
     *
     * @throws InvalidArgumentException
     */
    public function __construct($factory, $createDefault = 0, $forceDefault = FALSE) {
        parent::__construct();
        $this->monitor('NPresenter');

        try {
            $this->factoryCallback = callback($factory);
        } catch (InvalidArgumentException $e) {
            $type = is_object($factory) ? 'instanceof ' . get_class($factory) : gettype($factory);
            throw new InvalidArgumentException(
                    'KdybyReplicator requires callable factory, ' . $type . ' given.', 0, $e
            );
        }

        $this->createDefault = (int) $createDefault;
        $this->forceDefault = $forceDefault;
    }

    /**
     * @param callable $factory
     */
    public function setFactory($factory) {
        $this->factoryCallback = callback($factory);
    }

    /**
     * Magical component factory
     *
     * @param IComponentContainer
     */
    protected function attached($obj) {
        parent::attached($obj);

        if (!$obj instanceof NPresenter) {
            return;
        }

        $this->loadHttpData();
        $this->createDefault();
    }

    /**
     * @param boolean $recursive
     * @return ArrayIterator|NFormContainer[]
     */
    public function getContainers($recursive = FALSE) {
        return $this->getComponents($recursive, 'NFormContainer');
    }

    /**
     * @param boolean $recursive
     * @return ArrayIterator|NSubmitButton
     */
    public function getButtons($recursive = FALSE) {
        return $this->getComponents($recursive, 'ISubmitterControl');
    }

    /**
     * Magical component factory
     *
     * @param string $name
     * @return NFormContainer
     */
    protected function createComponent($name) {
        $NFormContainer = $this->createContainer($name);
        $NFormContainer->currentGroup = $this->currentGroup;
        $this->addComponent($NFormContainer, $name, $this->getFirstControlName());

        $this->factoryCallback->invoke($NFormContainer);

        return $this->created[$NFormContainer->name] = $NFormContainer;
    }

    /**
     * @return string
     */
    private function getFirstControlName() {
        $controls = iterator_to_array($this->getComponents(FALSE, 'IFormControl'));
        $firstControl = reset($controls);
        return $firstControl ? $firstControl->name : NULL;
    }

    /**
     * @param string $name
     *
     * @return NFormContainer
     */
    protected function createContainer($name) {
        $class = $this->containerClass;
        return new $class();
    }

    /**
     * @return boolean
     */
    public function isSubmittedBy() {
        if ($this->submittedBy) {
            return TRUE;
        }

        foreach ($this->getButtons(TRUE) as $button) {
            if ($button->isSubmittedBy()) {
                return $this->submittedBy = TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Create new NFormContainer
     *
     * @param string|int $name
     *
     * @throws InvalidArgumentException
     * @return NFormContainer
     */
    public function createOne($name = NULL) {
        if ($name === NULL) {
            $names = array_keys(iterator_to_array($this->getContainers()));
            $name = $names ? max($names) + 1 : 0;
        }

        // NFormContainer is overriden, therefore every request for getComponent($name, FALSE) would return NFormContainer
        if (isset($this->created[$name])) {
            throw new InvalidArgumentException("NFormContainer with name '$name' already exists.");
        }

        return $this[$name];
    }

    /**
     * @param array|Traversable $values
     * @param bool $erase
     * @return NFormContainer|KdybyReplicator
     */
    public function setValues($values, $erase = FALSE) {
        foreach ($values as $name => $value) {
            if ((is_array($value) || $value instanceof Traversable) && !$this->getComponent($name, FALSE)) {
                $this->createOne($name);
            }
        }

        return parent::setValues($values, $erase);
    }

    /**
     * Loads data received from POST
     * @internal
     */
    protected function loadHttpData() {
        if (!$this->getForm()->isSubmitted()) {
            return;
        }

        $this->setValues((array) $this->getHttpData());
    }

    /**
     * Creates default containers
     * @internal
     */
    protected function createDefault() {
        if (!$this->createDefault) {
            return;
        }

        if (!$this->getForm()->isSubmitted()) {
            foreach (range(0, $this->createDefault - 1) as $key) {
                $this->createOne($key);
            }
        } elseif ($this->forceDefault) {
            while (iterator_count($this->getContainers()) < $this->createDefault) {
                $this->createOne();
            }
        }
    }

    /**
     * @param string $name
     * @return array|null
     */
    protected function getContainerValues($name) {
        $post = $this->getHttpData();
        return isset($post[$name]) ? $post[$name] : NULL;
    }

    /**
     * @return mixed|NULL
     */
    private function getHttpData() {
        if ($this->httpPost === NULL) {
            $path = explode(self::NAME_SEPARATOR, $this->lookupPath('NForm'));
            $this->httpPost = NArrays::get($this->getForm()->getHttpData(), $path, NULL);
        }

        return $this->httpPost;
    }

    /**
     * @internal
     * @param NPresenterRequest $request
     * @return KdybyReplicator
     */
    public function setRequest(NPresenterRequest $request) {
        $this->httpRequest = $request;
        return $this;
    }

    /**
     * @return NPresenterRequest
     */
    private function getRequest() {
        if ($this->httpRequest !== NULL) {
            return $this->httpRequest;
        }

        return $this->httpRequest = $this->getForm()->getPresenter()->getRequest();
    }

    /**
     * @param NFormContainer $NFormContainer
     * @param boolean $cleanUpGroups
     *
     * @throws InvalidArgumentException
     * @return void
     */
    public function remove(NFormContainer $NFormContainer, $cleanUpGroups = FALSE) {
        if (!$NFormContainer->parent === $this) {
            throw new InvalidArgumentException('Given component ' . $NFormContainer->name . ' is not children of ' . $this->name . '.');
        }

        // to check if form was submitted by this one
        foreach ($NFormContainer->getComponents(TRUE, 'ISubmitterControl') as $button) {
            /** @var \Nette\Forms\Controls\SubmitButton $button */
            if ($button->isSubmittedBy()) {
                $this->submittedBy = TRUE;
                break;
            }
        }

        /** @var \Nette\Forms\Controls\BaseControl[] $components */
        $components = $NFormContainer->getComponents(TRUE);
        $this->removeComponent($NFormContainer);

        // reflection is required to hack form groups
        $groupRefl = NClassReflection::from('NFormGroup');

        $controlsProperty = $groupRefl->getProperty('controls');
        $controlsProperty->setAccessible(TRUE);

        // walk groups and clean then from removed components
        $affected = array();
        foreach ($this->getForm()->getGroups() as $group) {
            /** @var \SplObjectStorage $groupControls */
            $groupControls = $controlsProperty->getValue($group);

            foreach ($components as $control) {
                if ($groupControls->contains($control)) {
                    $groupControls->detach($control);

                    if (!in_array($group, $affected, TRUE)) {
                        $affected[] = $group;
                    }
                }
            }
        }

        // remove affected & empty groups
        if ($cleanUpGroups && $affected) {
            foreach ($this->getForm()->getComponents(FALSE, 'NFormContainer') as $NFormContainer) {
                if ($index = array_search($NFormContainer->currentGroup, $affected, TRUE)) {
                    unset($affected[$index]);
                }
            }

            /** @var \NFormGroup[] $affected */
            foreach ($affected as $group) {
                if (!$group->getControls() && in_array($group, $this->getForm()->getGroups(), TRUE)) {
                    $this->getForm()->removeGroup($group);
                }
            }
        }
    }

    /**
     * Counts filled values, filtered by given names
     *
     * @param array $components
     * @param array $subComponents
     * @return int
     */
    public function countFilledWithout(array $components = array(), array $subComponents = array()) {
        $httpData = array_diff_key((array) $this->getHttpData(), array_flip($components));

        if (!$httpData) {
            return 0;
        }

        $rows = array();
        $subComponents = array_flip($subComponents);
        foreach ($httpData as $item) {
            $filtered = array_filter(array_diff_key($item, $subComponents));
            $rows[] =  $filtered ? $filtered : FALSE;
        }

        return count(array_filter($rows));
    }

    /**
     * @param array $exceptChildren
     * @return bool
     */
    public function isAllFilled(array $exceptChildren = array()) {
        $components = array();
        foreach ($this->getComponents(FALSE, 'IFormControl') as $control) {
            /** @var \Nette\Forms\Controls\BaseControl $control */
            $components[] = $control->getName();
        }

        foreach ($this->getContainers() as $NFormContainer) {
            foreach ($NFormContainer->getComponents(TRUE, 'ISubmitterControl') as $button) {
                /** @var \Nette\Forms\Controls\SubmitButton $button */
                $exceptChildren[] = $button->getName();
            }
        }

        $filled = $this->countFilledWithout($components, array_unique($exceptChildren));
        return $filled === iterator_count($this->getContainers());
    }

    /**
     * @var bool
     */
    private static $registered = FALSE;

    /**
     * @param string $methodName
     * @return void
     */
    public static function register($methodName = 'addDynamic') {
        if (self::$registered) {
            NFormContainer::extensionMethod(self::$registered, 'KdybyReplicator::alreadyRegistered');
        }

        NFormContainer::extensionMethod($methodName, 'KdybyReplicator::extendContainer');

        if (self::$registered) {
            return;
        }

        NSubmitButton::extensionMethod('addRemoveOnClick', 'KdybyReplicator::addRemoveOnClick');

        NSubmitButton::extensionMethod('addCreateOnClick', 'KdybyReplicator::addCreateOnClick');

        self::$registered = $methodName;
    }

    // --- "anonymous" functions for registration ---
    public static function alreadyRegistered() {
        throw new MemberAccessException();
    }

    public static function extendContainer(NFormContainer $_this, $name, $factory, $createDefault = 0) {
        return $_this[$name] = new KdybyReplicator($factory, $createDefault);
    }

    public static function addRemoveOnClick(NSubmitButton $_this, $callback = NULL) {
        $KdybyReplicator = $_this->lookup('KdybyReplicator');
        $_this->setValidationScope(FALSE);
        $handler = new ReplicatorRemoveButtonOnClickHandler($KdybyReplicator, $callback);
        $_this->onClick[] = array($handler, 'invoke');
        return $_this;
    }

    public static function addCreateOnClick(NSubmitButton $_this, $allowEmpty = FALSE, $callback = NULL) {
        $KdybyReplicator = $_this->lookup('KdybyReplicator');
        $_this->setValidationScope(FALSE);
        $handler = new ReplicatorCreateButtonOnClickHandler($KdybyReplicator, $allowEmpty, $callback);
        $_this->onClick[] = array($handler, 'invoke');
        return $_this;
    }

}

class ReplicatorRemoveButtonOnClickHandler {

    private $callback;
    private $kdybyReplicator;

    public function __construct($kdybyReplicator, $callback) {
        $this->callback = $callback;
        $this->kdybyReplicator = $kdybyReplicator;
    }

    public function invoke(NSubmitButton $button) {
        if (is_callable($this->callback)) {
            callback($this->callback)->invoke($this->kdybyReplicator, $button->parent);
        }
        $this->kdybyReplicator->remove($button->parent);
    }

}

class ReplicatorCreateButtonOnClickHandler {

    private $callback;
    private $allowEmpty;
    private $kdybyReplicator;

    public function __construct($kdybyReplicator, $allowEmpty, $callback) {
        $this->callback = $callback;
        $this->allowEmpty = $allowEmpty;
        $this->kdybyReplicator = $kdybyReplicator;
    }

    public function invoke(NSubmitButton $button) {
        /** @var KdybyReplicator $KdybyReplicator */
        if (!is_bool($this->allowEmpty)) {
            $this->callback = callback($this->allowEmpty);
            $this->allowEmpty = FALSE;
        }
        if ($this->allowEmpty === FALSE && $this->kdybyReplicator->isAllFilled() === FALSE) {
            return;
        }
        $newContainer = $this->kdybyReplicator->createOne();
        if (is_callable($this->callback)) {
            callback($this->callback)->invoke($this->kdybyReplicator, $newContainer);
        }
    }

}



<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class WizardComponent extends NControl {

    const ID_ELEMENT = 'wizardId';

    /**
     * @var array of callback(NAppForm $formToInit)
     */
    public $onStepInit;

    /**
     * Data to process is array each step's values stored with key from the step's name.
     * @var array of callback(WizardComponent $wizard)
     */
    public $onProcess;

    /**
     * @var array of str|callback(NAppForm $submittedForm)
     */
    private $nextCallbacks = array();

    /**
     * @var str
     */
    private $firstStepName;

    /**
     * @var str
     */
    private $currentStepName;

    /**
     * @var str  id that allows running more instances of wizard in different tabs
     */
    private $wizardId;

    /**
     * @param NAppForm $form                form displayed in the step
     * @param str $name                     name of the step (for reference)
     * @param callback|str $nextCallback    name of the following step or callback
     *                                      that should return name of the following step
     */
    public function addStep(NAppForm $form, $name, $nextCallback = null) {
        $form->addHidden(self::ID_ELEMENT);
        $form->onSuccess[] = array($this, 'stepSubmitted');
        $this->addComponent($form, $name);
        if ($nextCallback !== null) {
            $this->nextCallbacks[$name] = $nextCallback;
        }
    }

    /**
     * @param str $name
     */
    public function setFirstStep($name) {
        $this->firstStepName = $name;
    }

    /**
     * @return str
     */
    public function getFirstStep() {
        return $this->firstStepName;
    }

    /**
     * @return str
     */
    public function getCurrentStep() {
        if ($this->currentStepName === null) {
            $this->currentStepName = $this->getFirstStep();
        }
        return $this->currentStepName;
    }

    /**
     * @param str $name
     */
    private function setCurrentStep($name) {
        $this->currentStepName = $name;
    }

    /**
     * 
     * @param str $name
     * @return array|null
     */
    public function getData($name) {
        $session = $this->getSession();
        return isset($session->$name) ? (array) $session->$name : null;
    }

    /**
     * Free data from session.
     * 
     * @return void
     */
    public function disposeData() {
        $this->getSession()->remove();
    }

    // -------------------------------------------
    private function getWizardId() {
        if ($this->wizardId === null) {
            $this->wizardId = uniqid('', true);
        }
        return $this->wizardId;
    }

    private function setWizardId($wizardId) {
        $this->wizardId = $wizardId;
    }

    public function render() {
        $name = $this->getCurrentStep();
        $currentForm = $this->getComponent($name);
        $this->onStepInit($currentForm);
        $currentForm[self::ID_ELEMENT]->setValue($this->getWizardId());
        $currentForm->render();
    }

    /**
     * @interal
     * @param AppForm $form
     */
    public function stepSubmitted(NAppForm $form) {
        // realize where we are
        $name = $form->getName();
        $this->setCurrentStep($name);

        // store data to session
        $values = $form->getValues();
        $this->setWizardId($values[self::ID_ELEMENT]);
        unset($values[self::ID_ELEMENT]);

        $session = $this->getSession();
        $session->$name = $values;


        // find next component
        if (isset($this->nextCallbacks[$name])) {
            $next = $this->nextCallbacks[$name];
            if (is_string($next) && $this->getComponent($next, false)) {
                $newName = $next;
            } else {
                $newName = call_user_func($this->nextCallbacks[$name], $form);
            }
            if ($newName === null) {
                $this->onProcess($this);
            } else {
                $this->setCurrentStep($newName);
            }
        } else {// process data
            $this->onProcess($this);
        }
    }

    private function getSession() {
        return $this->getPresenter()->getSession($this->getWizardId());
    }

}

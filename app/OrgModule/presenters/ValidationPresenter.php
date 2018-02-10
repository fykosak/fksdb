<?php

namespace OrgModule;

use Nette\Application\BadRequestException;
use Nette\Diagnostics\Debugger;

class ValidationPresenter extends BasePresenter {
    /**
     * @var \ValidationTest[]
     */
    private $tests = [];
    /**
     * @var \ValidationTest
     */
    private $selectedTest = null;

    public function __construct(\ServicePerson $servicePerson) {
        parent::__construct();
        $this->tests[] = new \ParticipantsDurationTest($servicePerson);
    }

    public function startup() {
        parent::startup();
        foreach ($this->tests as $test) {
            if ($test->getAction() === $this->getAction()) {
                $this->selectedTest = $test;
                $this->selectedTest->run();
            }
        }
    }

    public function titleTest() {
        $this->setTitle($this->selectedTest->getTitle());
    }

    public function titleDefault() {
        $this->setTitle('Validačné testy');
    }

    public function beforeRender() {
        parent::beforeRender();
        if ($this->selectedTest) {
            $this->setView('test');
        }
    }

    public function renderDefault() {
        $this->template->tests = $this->tests;
    }

    public function createComponentTest() {
        return $this->selectedTest->getComponent();
    }

    public function getNavBarVariant() {
        return [null, null];
    }

}



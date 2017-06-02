<?php

namespace OrgModule;

use FKSDB\Components\Controls\Stalking\Address;
use FKSDB\Components\Controls\Stalking\BaseInfo;
use FKSDB\Components\Controls\Stalking\Contestant;
use FKSDB\Components\Controls\Stalking\EventOrg;
use FKSDB\Components\Controls\Stalking\EventParticipant;
use FKSDB\Components\Controls\Stalking\Login;
use FKSDB\Components\Controls\Stalking\Org;
use FKSDB\Components\Controls\Stalking\PersonHistory;

class StalkingPresenter extends BasePresenter {

    /**
     * @var \ServicePerson
     */
    private $servicePerson;

    /**
     * @var \ServiceEvent
     */
    private $serviceEvent;

    /**
     * @var \ModelPerson
     */
    private $person = null;

    /**
     * @return \ModelPerson|null
     */
    private function getPerson() {
        if (is_null($this->person)) {
            $id = $this->getParameter('id');
            $this->person = $this->servicePerson->findByPrimary($id);
        }
        return $this->person;
    }

    public function injectServicePerson(\ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    public function injectServiceEvent(\ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    public function createComponentBaseInfo() {
        $component = new BaseInfo($this->getPerson());
        return $component;
    }

    public function createComponentAddress() {
        $component = new Address($this->getPerson());
        return $component;
    }

    public function createComponentEventParticipant() {
        $component = new EventParticipant($this->getPerson());
        return $component;
    }

    public function createComponentEventOrg() {
        $component = new EventOrg($this->getPerson());
        return $component;
    }

    public function createComponentLogin() {
        $component = new Login($this->getPerson());
        return $component;
    }

    public function createComponentOrg() {
        $component = new Org($this->getPerson());
        return $component;
    }

    public function createComponentContestant() {
        $component = new Contestant($this->getPerson());
        return $component;
    }

    public function createComponentPersonHistory() {
        $component = new PersonHistory($this->getPerson());
        return $component;
    }

    public function titleView() {
        if (is_null($this->getPerson())) {
            $this->setTitle('Stalking');
        } else {
            $this->setTitle('Stalking ' . $this->getPerson()->getFullname());
        }
    }
}

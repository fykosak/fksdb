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
use ModelPerson;
use Nette\Application\BadRequestException;
use ServiceEvent;
use ServicePerson;

class StalkingPresenter extends BasePresenter {

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * @var ModelPerson|null
     */
    private $person = false;

    /**
     * @return ModelPerson|null
     */
    private function getPerson() {
        if ($this->person === false) {
            $id = $this->getParameter('id');
            $this->person = $this->servicePerson->findByPrimary($id);
        }

        return $this->person;
    }

    public function authorizedDefault($id) {
        $person = $this->getPerson();
        if (!$person) {
            throw new BadRequestException('Neexistující osoba.', 404);
        }
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($person, 'stalk', $this->getSelectedContest()));
    }

    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    public function injectServiceEvent(ServiceEvent $serviceEvent) {
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

    public function titleDefault() {
        $this->setTitle(sprintf(_('Stalking %s'), $this->getPerson()->getFullname()));
    }

    public function renderDefault() {
        $this->template->person = $this->person;
    }

}

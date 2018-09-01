<?php

namespace OrgModule;

use FKS\Components\Controls\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Controls\Stalking\Address;
use FKSDB\Components\Controls\Stalking\BaseInfo;
use FKSDB\Components\Controls\Stalking\Contestant;
use FKSDB\Components\Controls\Stalking\EventOrg;
use FKSDB\Components\Controls\Stalking\EventParticipant;
use FKSDB\Components\Controls\Stalking\Login;
use FKSDB\Components\Controls\Stalking\Org;
use FKSDB\Components\Controls\Stalking\PersonHistory;
use FKSDB\Components\Controls\Stalking\Role;
use FKSDB\Components\Controls\Stalking\Flag;
use FKSDB\Components\Controls\Stalking\EventTeacher;
use FKSDB\Components\Forms\Factories\ReferencedPersonFactory;
use ModelPerson;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Persons\DenyResolver;
use Persons\ExtendedPersonHandler;
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
     * @var ReferencedPersonFactory
     */
    private $referencedPersonFactory;

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

    public function authorizedDefault() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('person', 'stalk-search', $this->getSelectedContest()));
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function authorizedView($id) {
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

    function injectReferencedPersonFactory(ReferencedPersonFactory $referencedPersonFactory) {
        $this->referencedPersonFactory = $referencedPersonFactory;
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

    public function createComponentEventTeacher() {
        $component = new EventTeacher($this->getPerson());
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

    public function createComponentRole() {
        $component = new Role($this->getPerson());
        return $component;
    }

    public function createComponentFlag() {
        $component = new Flag($this->getPerson());
        return $component;
    }

    public function createComponentFormSearch() {
        $control = new FormControl();
        $form = $control->getForm();
        $control->setGroupMode(FormControl::GROUP_CONTAINER);

        $container = new ContainerWithOptions();
        $form->addComponent($container, ExtendedPersonHandler::CONT_AGGR);

        $fieldsDefinition = [];
        $acYear = $this->getSelectedAcademicYear();
        $searchType = ReferencedPersonFactory::SEARCH_ID;
        $allowClear = true;
        $modifiabilityResolver = $visibilityResolver = new DenyResolver();
        $components = $this->referencedPersonFactory->createReferencedPerson($fieldsDefinition, $acYear, $searchType, $allowClear, $modifiabilityResolver, $visibilityResolver);
        $components[0]->addRule(Form::FILLED, _('Osobu je třeba zadat.'));
        $components[1]->setOption('label', _('Osoba'));

        $container->addComponent($components[0], ExtendedPersonHandler::EL_PERSON);
        $container->addComponent($components[1], ExtendedPersonHandler::CONT_PERSON);

        $submit = $form->addSubmit('send', _('Stalkovat'));
        $submit->onClick[] = function (SubmitButton $button) {
            $form = $button->getForm();
            $values = $form->getValues();
            $id = $values[ExtendedPersonHandler::CONT_AGGR][ExtendedPersonHandler::EL_PERSON];
            $this->redirect('view', ['id' => $id]);
        };

        return $control;
    }

    public function titleDefault() {
        $this->setTitle(_('Stalking'));
    }

    public function titleView($id) {
        $this->setTitle(sprintf(_('Stalking %s'), $this->getPerson()->getFullname()));
    }

    protected function getNavBarVariant() {
        /**
         * @var $contest \ModelContest
         */
        $contest = $this->serviceContest->findByPrimary($this->contestId);
        if ($contest) {
            return [$contest->getContestSymbol(), 'dark'];
        }
        return [null, null];
    }

}

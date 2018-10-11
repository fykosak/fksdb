<?php

namespace OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Stalking\Address;
use FKSDB\Components\Controls\Stalking\BaseInfo;
use FKSDB\Components\Controls\Stalking\Contestant;
use FKSDB\Components\Controls\Stalking\EventOrg;
use FKSDB\Components\Controls\Stalking\EventParticipant;
use FKSDB\Components\Controls\Stalking\EventTeacher;
use FKSDB\Components\Controls\Stalking\Flag;
use FKSDB\Components\Controls\Stalking\Login;
use FKSDB\Components\Controls\Stalking\Org;
use FKSDB\Components\Controls\Stalking\PersonHistory;
use FKSDB\Components\Controls\Stalking\Role;
use FKSDB\Components\Controls\Stalking\StalkingComponent;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\ORM\ModelPerson;
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
     * @var string
     */
    private $mode;

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
     * @throws BadRequestException
     */
    public function authorizedView() {
        $person = $this->getPerson();
        if (!$person) {
            throw new BadRequestException('Neexistující osoba.', 404);
        }

        $full = $this->getContestAuthorizator()->isAllowed($person, 'stalk.full', $this->getSelectedContest());

        $restrict = $this->getContestAuthorizator()->isAllowed($person, 'stalk.restrict', $this->getSelectedContest());

        $basic = $this->getContestAuthorizator()->isAllowed($person, 'stalk.basic', $this->getSelectedContest());

        $this->setAuthorized($full || $restrict || $basic);
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
        $component = new BaseInfo($this->getPerson(), $this->getMode());
        return $component;
    }

    public function createComponentAddress() {
        $component = new Address($this->getPerson(), $this->getMode());
        return $component;
    }

    public function createComponentEventParticipant() {
        $component = new EventParticipant($this->getPerson(), $this->getMode());
        return $component;
    }

    public function createComponentEventTeacher() {
        $component = new EventTeacher($this->getPerson(), $this->getMode());
        return $component;
    }

    public function createComponentEventOrg() {
        $component = new EventOrg($this->getPerson(), $this->getMode());
        return $component;
    }

    public function createComponentLogin() {
        $component = new Login($this->getPerson(), $this->getMode());
        return $component;
    }

    public function createComponentOrg() {
        $component = new Org($this->getPerson(), $this->getMode());
        return $component;
    }

    public function createComponentContestant() {
        $component = new Contestant($this->getPerson(), $this->getMode());
        return $component;
    }

    public function createComponentPersonHistory() {
        $component = new PersonHistory($this->getPerson(), $this->getMode());
        return $component;
    }

    public function createComponentRole() {
        $component = new Role($this->getPerson(), $this->getMode());
        return $component;
    }

    public function createComponentFlag() {
        $component = new Flag($this->getPerson(), $this->getMode());
        return $component;
    }

    public function createComponentFormSearch() {
        $control = new FormControl();
        $form = $control->getForm();

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

    private function getMode() {
        if (!$this->mode) {
            if ($this->getContestAuthorizator()->isAllowed($this->getPerson(), 'stalk.basic', $this->getSelectedContest())) {
                $this->mode = StalkingComponent::PERMISSION_BASIC;
            }
            if ($this->getContestAuthorizator()->isAllowed($this->getPerson(), 'stalk.restrict', $this->getSelectedContest())) {
                $this->mode = StalkingComponent::PERMISSION_RESTRICT;
            }
            if ($this->getContestAuthorizator()->isAllowed($this->getPerson(), 'stalk.full', $this->getSelectedContest())) {
                $this->mode = StalkingComponent::PERMISSION_FULL;
            }
        }
        return $this->mode;
    }

    public function titleDefault() {
        $this->setTitle(_('Stalking'));
        $this->setIcon('fa fa-search');
    }

    public function titleView() {
        $this->setTitle(sprintf(_('Stalking %s'), $this->getPerson()->getFullname()));
        $this->setIcon('fa fa-eye');
    }

    protected function getNavBarVariant() {
        /**
         * @var $contest \FKSDB\ORM\ModelContest
         */
        $contest = $this->serviceContest->findByPrimary($this->contestId);
        if ($contest) {
            return [$contest->getContestSymbol(), 'dark'];
        }
        return [null, null];
    }

}

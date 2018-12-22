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
     * @var ModelPerson
     */
    private $person;
    /**
     * @var string
     */
    private $mode;

    /**
     * @return ModelPerson
     * @throws BadRequestException
     */
    private function getPerson(): ModelPerson {
        if ($this->person === false) {
            $id = $this->getParameter('id');
            $row = $this->servicePerson->findByPrimary($id);
            if (!$row) {
                throw new BadRequestException(_('Osoba neexistuje'), 404);
            }
            $this->person = ModelPerson::createFromTableRow($row);
        }

        return $this->person;
    }

    public function authorizedDefault() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('person', 'stalk.search', $this->getSelectedContest()));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedView() {
        $person = $this->getPerson();

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

    /**
     * @return BaseInfo
     * @throws BadRequestException
     */
    public function createComponentBaseInfo(): BaseInfo {
        return new BaseInfo($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Address
     * @throws BadRequestException
     */
    public function createComponentAddress(): Address {
        return new Address($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return EventParticipant
     * @throws BadRequestException
     */
    public function createComponentEventParticipant(): EventParticipant {
        return new EventParticipant($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return EventTeacher
     * @throws BadRequestException
     */
    public function createComponentEventTeacher() {
        return new EventTeacher($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return EventOrg
     * @throws BadRequestException
     */
    public function createComponentEventOrg(): EventOrg {
        return new EventOrg($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Login
     * @throws BadRequestException
     */
    public function createComponentLogin(): Login {
        return new Login($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Org
     * @throws BadRequestException
     */
    public function createComponentOrg(): Org {
        return new Org($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Contestant
     * @throws BadRequestException
     */
    public function createComponentContestant(): Contestant {
        return new Contestant($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return PersonHistory
     * @throws BadRequestException
     */
    public function createComponentPersonHistory(): PersonHistory {
        return new PersonHistory($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Role
     * @throws BadRequestException
     */
    public function createComponentRole(): Role {
        return new Role($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Flag
     * @throws BadRequestException
     */
    public function createComponentFlag(): Flag {
        return new Flag($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    public function createComponentFormSearch(): FormControl {
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

    /**
     * @return string
     * @throws BadRequestException
     */
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

    /**
     * @throws BadRequestException
     */
    public function titleView() {
        $this->setTitle(sprintf(_('Stalking %s'), $this->getPerson()->getFullname()));
        $this->setIcon('fa fa-eye');
    }
}

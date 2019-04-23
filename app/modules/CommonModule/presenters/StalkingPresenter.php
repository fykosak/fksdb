<?php

namespace CommonModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Stalking;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ValidationTest\ValidationFactory;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Persons\DenyResolver;
use Persons\ExtendedPersonHandler;

/**
 * Class StalkingPresenter
 * @package OrgModule
 */
class StalkingPresenter extends BasePresenter {

    /**
     * @var \FKSDB\ORM\Services\ServicePerson
     */
    private $servicePerson;

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
     * @var ValidationFactory
     */
    private $validationFactory;

    /**
     * @param \FKSDB\ORM\Services\ServicePerson $servicePerson
     */
    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param ReferencedPersonFactory $referencedPersonFactory
     */
    public function injectReferencedPersonFactory(ReferencedPersonFactory $referencedPersonFactory) {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    /**
     * @param ValidationFactory $validationFactory
     */
    public function injectValidationFactory(ValidationFactory $validationFactory) {
        $this->validationFactory = $validationFactory;
    }

    public function titleDefault() {
        $this->setTitle(_('Stalking'));
        $this->setIcon('fa fa-search');
    }

    /**
     * @throws BadRequestException
     */
    public function titleView() {
        $this->setTitle(sprintf(_('Stalking %s'), $this->getPerson()->getFullName()));
        $this->setIcon('fa fa-eye');
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        $this->setAuthorized($this->isAllowed('person', 'stalk.search'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedView() {
        $person = $this->getPerson();

        $full = $this->isAllowed($person, 'stalk.full');

        $restrict = $this->isAllowed($person, 'stalk.restrict');

        $basic = $this->isAllowed($person, 'stalk.basic');

        $this->setAuthorized($full || $restrict || $basic);
    }

    /**
     * @return Stalking\BaseInfo
     * @throws BadRequestException
     */
    public function createComponentBaseInfo(): Stalking\BaseInfo {
        return new Stalking\BaseInfo($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\Address
     * @throws BadRequestException
     */
    public function createComponentAddress(): Stalking\Address {
        return new Stalking\Address($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\EventParticipant
     * @throws BadRequestException
     */
    public function createComponentEventParticipant(): Stalking\EventParticipant {
        return new Stalking\EventParticipant($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\EventTeacher
     * @throws BadRequestException
     */
    public function createComponentEventTeacher(): Stalking\EventTeacher {
        return new Stalking\EventTeacher($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\EventOrg
     * @throws BadRequestException
     */
    public function createComponentEventOrg(): Stalking\EventOrg {
        return new Stalking\EventOrg($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\Login
     * @throws BadRequestException
     */
    public function createComponentLogin(): Stalking\Login {
        return new Stalking\Login($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\Org
     * @throws BadRequestException
     */
    public function createComponentOrg(): Stalking\Org {
        return new Stalking\Org($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\Contestant
     * @throws BadRequestException
     */
    public function createComponentContestant(): Stalking\Contestant {
        return new Stalking\Contestant($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\PersonHistory
     * @throws BadRequestException
     */
    public function createComponentPersonHistory(): Stalking\PersonHistory {
        return new Stalking\PersonHistory($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\Role
     * @throws BadRequestException
     */
    public function createComponentRole(): Stalking\Role {
        return new Stalking\Role($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\Flag
     * @throws BadRequestException
     */
    public function createComponentFlag(): Stalking\Flag {
        return new Stalking\Flag($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\Payment
     * @throws BadRequestException
     */
    public function createComponentPayment(): Stalking\Payment {
        return new Stalking\Payment($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\ContactInfo
     * @throws BadRequestException
     */
    public function createComponentContactInfo(): Stalking\ContactInfo {
        return new Stalking\ContactInfo($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\AcademicDegree
     * @throws BadRequestException
     */
    public function createComponentAcademicDegree(): Stalking\AcademicDegree {
        return new Stalking\AcademicDegree($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\Schedule
     * @throws BadRequestException
     */
    public function createComponentSchedule(): Stalking\Schedule {
        return new Stalking\Schedule($this->getPerson(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\Validation
     * @throws BadRequestException
     */
    public function createComponentValidation(): Stalking\Validation {
        return new Stalking\Validation($this->validationFactory, $this->getPerson(), $this->getTranslator(), $this->getMode());
    }


    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws \Nette\Utils\RegexpException
     */
    public function createComponentFormSearch(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();

        $container = new ContainerWithOptions();
        $form->addComponent($container, ExtendedPersonHandler::CONT_AGGR);

        $fieldsDefinition = [];
        //$acYear = $this->getSelectedAcademicYear();
        $searchType = ReferencedPersonFactory::SEARCH_ID;
        $allowClear = true;
        $modifiabilityResolver = $visibilityResolver = new DenyResolver();
        $components = $this->referencedPersonFactory->createReferencedPerson($fieldsDefinition, null, $searchType, $allowClear, $modifiabilityResolver, $visibilityResolver);
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
            if ($this->isAllowed($this->getPerson(), 'stalk.basic')) {
                $this->mode = Stalking\StalkingComponent::PERMISSION_BASIC;
            }
            if ($this->isAllowed($this->getPerson(), 'stalk.restrict')) {
                $this->mode = Stalking\StalkingComponent::PERMISSION_RESTRICT;
            }
            if ($this->isAllowed($this->getPerson(), 'stalk.full')) {
                $this->mode = Stalking\StalkingComponent::PERMISSION_FULL;
            }
        }
        return $this->mode;
    }

    /**
     * @return ModelPerson
     * @throws BadRequestException
     */
    private function getPerson(): ModelPerson {
        if (!$this->person) {
            $id = $this->getParameter('id');
            $row = $this->servicePerson->findByPrimary($id);
            if (!$row) {
                throw new BadRequestException(_('Osoba neexistuje'), 404);
            }
            $this->person = ModelPerson::createFromActiveRow($row);
        }

        return $this->person;
    }
}

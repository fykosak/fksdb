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
     * @var Stalking\StalkingService
     */
    private $stalkingService;

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

    /**
     * @param Stalking\StalkingService $stalkingService
     */
    public function injectStalkingService(Stalking\StalkingService $stalkingService) {
        $this->stalkingService = $stalkingService;
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
     * @return \FKSDB\Components\Controls\Stalking\StalkingComponent\StalkingComponent
     * @throws BadRequestException
     */
    public function createComponentStalkingComponent(): Stalking\StalkingComponent\StalkingComponent {
        return new Stalking\StalkingComponent\StalkingComponent($this->stalkingService, $this->getPerson(), $this->getTableReflectionFactory(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\Address
     * @throws BadRequestException
     */
    public function createComponentAddress(): Stalking\Address {
        return new Stalking\Address($this->getPerson(), $this->getTableReflectionFactory(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\EventParticipant
     * @throws BadRequestException
     */
    public function createComponentEventParticipant(): Stalking\EventParticipant {
        return new Stalking\EventParticipant($this->getPerson(), $this->getTableReflectionFactory(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\EventTeacher
     * @throws BadRequestException
     */
    public function createComponentEventTeacher(): Stalking\EventTeacher {
        return new Stalking\EventTeacher($this->getPerson(), $this->getTableReflectionFactory(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\EventOrg
     * @throws BadRequestException
     */
    public function createComponentEventOrg(): Stalking\EventOrg {
        return new Stalking\EventOrg($this->getPerson(), $this->getTableReflectionFactory(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\Contestant
     * @throws BadRequestException
     */
    public function createComponentContestant(): Stalking\Contestant {
        return new Stalking\Contestant($this->getPerson(), $this->getTableReflectionFactory(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\Role
     * @throws BadRequestException
     */
    public function createComponentRole(): Stalking\Role {
        return new Stalking\Role($this->getPerson(), $this->getTableReflectionFactory(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\Flag
     * @throws BadRequestException
     */
    public function createComponentFlag(): Stalking\Flag {
        return new Stalking\Flag($this->getPerson(), $this->getTableReflectionFactory(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\Schedule
     * @throws BadRequestException
     */
    public function createComponentSchedule(): Stalking\Schedule {
        return new Stalking\Schedule($this->getPerson(), $this->getTableReflectionFactory(), $this->getTranslator(), $this->getMode());
    }

    /**
     * @return Stalking\Validation
     * @throws BadRequestException
     */
    public function createComponentValidation(): Stalking\Validation {
        return new Stalking\Validation($this->validationFactory, $this->getTableReflectionFactory(), $this->getPerson(), $this->getTranslator(), $this->getMode());
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
                $this->mode = Stalking\AbstractStalkingComponent::PERMISSION_BASIC;
            }
            if ($this->isAllowed($this->getPerson(), 'stalk.restrict')) {
                $this->mode = Stalking\AbstractStalkingComponent::PERMISSION_RESTRICT;
            }
            if ($this->isAllowed($this->getPerson(), 'stalk.full')) {
                $this->mode = Stalking\AbstractStalkingComponent::PERMISSION_FULL;
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

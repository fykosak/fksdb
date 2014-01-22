<?php

namespace FKSDB\Components\Factories;

use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\ContestantFactory;
use FKSDB\Components\Forms\Factories\OrgFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\WizardComponent;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Kdyby\Extension\Forms\Replicator\Replicator;
use ModelContest;
use Nette\Application\UI\Form;
use Nette\Http\Session;
use Nette\Utils\Html;
use ServicePerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ExtendedPersonWizardFactory {

    const STEP_PERSON = 'person';
    const STEP_DATA = 'data';
    const SEND = 'send';

    /* Important elements */
    const EL_PERSON_ID = 'person_id';
    const EL_EMAIL = 'email';
    /* Containers */
    const CONT_PERSON = 'person';
    const CONT_PERSON_HISTORY = 'person_history';
    const CONT_PERSON_INFO = 'person_info';
    const CONT_ADDRESSES = 'addresses';
    const CONT_ORG = 'org';

    /* Important groups */
    const GRP_PERSON = 'personGrp';

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var ContestantFactory
     */
    private $contestantFactory;

    /**
     * @var OrgFactory
     */
    private $orgFactory;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var ServicePerson
     */
    private $personService;

    /**
     *
     * @var PersonProvider
     */
    private $personProvider;

    /**
     * @var Session
     */
    private $session;

    function __construct(PersonFactory $personFactory, ContestantFactory $contestantFactory, OrgFactory $orgFactory, AddressFactory $addressFactory, ServicePerson $personService, PersonProvider $personProvider, Session $session) {
        $this->personFactory = $personFactory;
        $this->contestantFactory = $contestantFactory;
        $this->orgFactory = $orgFactory;
        $this->addressFactory = $addressFactory;
        $this->personService = $personService;
        $this->personProvider = $personProvider;
        $this->session = $session;
    }

    private function createWizardBase() {
        $wizard = new WizardComponent($this->session);

        $wizard->setFirstStep(self::STEP_PERSON);

        $personForm = $this->createPersonForm();
        $wizard->addStep($personForm, self::STEP_PERSON, self::STEP_DATA);
        $wizard->registerStepSubmitter(self::STEP_PERSON, self::SEND);

        return $wizard;
    }

    /**
     * 
     * @return WizardComponent
     */
    public function createContestant($acYear) {
        $wizard = $this->createWizardBase();

        $dataForm = $this->createContestantForm($acYear);
        $wizard->addStep($dataForm, self::STEP_DATA);
        $wizard->registerStepSubmitter(self::STEP_DATA, self::SEND);

        return $wizard;
    }

    /**
     * 
     * @return WizardComponent
     */
    public function createOrg(ModelContest $contest) {
        $wizard = $this->createWizardBase();

        $dataForm = $this->createOrgForm($contest);
        $wizard->addStep($dataForm, self::STEP_DATA);
        $wizard->registerStepSubmitter(self::STEP_DATA, self::SEND);

        return $wizard;
    }

    private function createPersonForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());

        $group = $form->addGroup(_('Existující osoba'));

        $personElement = $this->personFactory->createPersonSelect(true, _('Jméno'), $this->personProvider);

// TODO validate non-existent contestant or restrict selection
//        $personElement->addCondition(Form::FILLED)->toggle(self::GRP_PERSON, false);
        $form->addComponent($personElement, self::EL_PERSON_ID);

        $group = $form->addGroup(_('Nová osoba'));
        $group->setOption('container', Html::el('fieldset')->id(self::GRP_PERSON));
        $personContainer = $this->personFactory->createPerson(PersonFactory::SHOW_DISPLAY_NAME | PersonFactory::SHOW_GENDER, $group, array(
            PersonFactory::IDX_CONTROL => $personElement,
            PersonFactory::IDX_OPERATION => ~Form::FILLED,
            PersonFactory::IDX_VALUE => null,
        ));
        $form->addComponent($personContainer, self::CONT_PERSON);

        $form->setCurrentGroup();

        $form->addSubmit(self::SEND, _('Pokračovat'));
        return $form;
    }

    private function createContestantForm($acYear) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());

        /*
         * Person
         */
        $this->addPersonContainer($form);

        /*
         * Contestant
         */
        $group = $form->addGroup(_('Řešitel'));
        $historyContainer = $this->personFactory->createPersonHistory(PersonFactory::SHOW_LIKE_CONTESTANT, $group, $acYear);
        $form->addComponent($historyContainer, self::CONT_PERSON_HISTORY);


        /**
         * Addresses
         */
        $group = $form->addGroup(_('Adresa'));
        $factory = $this->addressFactory;
        $replicator = new Replicator(function($replContainer) use($factory, $group) {
                    $factory->buildAddress($replContainer, AddressFactory::SHOW_EXTENDED_ROWS, $group);
                    $replContainer->addComponent($factory->createTypeElement(), 'type');

                    $replContainer->addSubmit('remove', _('Odebrat adresu'))->addRemoveOnClick();
                }, 1, true);
        $replicator->containerClass = 'FKSDB\Components\Forms\Containers\AddressContainer';

        $form->addComponent($replicator, self::CONT_ADDRESSES);

        $replicator->addSubmit('add', _('Přidat adresu'))->addCreateOnClick();


        /**
         * Personal information
         */
        $group = $form->addGroup(_('Osobní informace'));
        $infoContainer = $this->personFactory->createPersonInfo(0, $group);
        $form->addComponent($infoContainer, self::CONT_PERSON_INFO);

        $form->setCurrentGroup();

        $form->addSubmit(self::SEND, _('Dokončit'));
        return $form;
    }

    private function createOrgForm(ModelContest $contest) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());

        /*
         * Person
         */
        $this->addPersonContainer($form);

        /*
         * Org
         */
        $group = $form->addGroup(_('Organizátor'));
        $orgContainer = $this->orgFactory->createOrg(null, $group, $contest);
        $form->addComponent($orgContainer, self::CONT_ORG);


        /**
         * Personal information
         */
        $group = $form->addGroup(_('Osobní informace'));
        $infoContainer = $this->personFactory->createPersonInfo(PersonFactory::SHOW_ORG_INFO, $group);
        $form->addComponent($infoContainer, self::CONT_PERSON_INFO);

        $form->setCurrentGroup();

        $form->addSubmit(self::SEND, _('Dokončit'));
        return $form;
    }

    protected final function addPersonContainer($form) {
        $group = $form->addGroup(_('Osoba'));
        $personContainer = $this->personFactory->createPerson(PersonFactory::DISABLED, $group);
        $form->addComponent($personContainer, self::CONT_PERSON);

        $this->personFactory->appendEmailWithLogin($personContainer, null, PersonFactory::SHOW_LOGIN_CREATION);
    }

}

<?php

namespace FKSDB\Components\Factories;

use FKS\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKS\Localization\GettextTranslator;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\ContestantFactory;
use FKSDB\Components\Forms\Factories\OrgFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Rules\UniqueEmailFactory;
use FKSDB\Components\WizardComponent;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Kdyby\Extension\Forms\Replicator\Replicator;
use ModelContest;
use ModelPerson;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
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
    const EL_CREATE_LOGIN = 'createLogin';
    const EL_CREATE_LOGIN_LANG = 'lang';
    /* Containers */
    const CONT_PERSON = 'person';
    const CONT_CONTESTANT = 'contestant';
    const CONT_PERSON_INFO = 'person_info';
    const CONT_ADDRESSES = 'addresses';
    const CONT_LOGIN = 'login';
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

    /**
     * @var UniqueEmailFactory
     */
    private $uniqueEmailFactory;

    /**
     * @var GettextTranslator
     */
    private $translator;

    function __construct(PersonFactory $personFactory, ContestantFactory $contestantFactory, OrgFactory $orgFactory, AddressFactory $addressFactory, ServicePerson $personService, PersonProvider $personProvider, Session $session, UniqueEmailFactory $uniqueEmailFactory, GettextTranslator $translator) {
        $this->personFactory = $personFactory;
        $this->contestantFactory = $contestantFactory;
        $this->orgFactory = $orgFactory;
        $this->addressFactory = $addressFactory;
        $this->personService = $personService;
        $this->personProvider = $personProvider;
        $this->session = $session;
        $this->uniqueEmailFactory = $uniqueEmailFactory;
        $this->translator = $translator;
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
    public function createContestant() {
        $wizard = $this->createWizardBase();

        $dataForm = $this->createContestantForm();
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

        $renderMethod = 'return $("<li>")
                        .append("<a>" + item.label + "<br>" + item.place + ", ID: " + item.value + "</a>")
                        .appendTo(ul);';
        $personElement = new AutocompleteSelectBox(true, 'Jméno', $renderMethod);
        $personElement->setDataProvider($this->personProvider);


// TODO validate non-existent contestant or restrict selection
        $personElement->addCondition(Form::FILLED)->toggle(self::GRP_PERSON, false);
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

    private function createContestantForm() {
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
        $contestantContainer = $this->contestantFactory->createContestant(null, $group);
        $form->addComponent($contestantContainer, self::CONT_CONTESTANT);


        /**
         * Addresses
         */
        $group = $form->addGroup(_('Adresa'));
        $factory = $this->addressFactory;
        $replicator = new Replicator(function($replContainer) use($factory, $group) {
                    $factory->buildAddress($replContainer, $group);
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

        $loginContainer = $this->createLoginContainer($group);
        $form->addComponent($loginContainer, self::CONT_LOGIN);
    }

    private function createLoginContainer($group) {
        $container = new Container();
        $container->setCurrentGroup($group);

        $email = $container->addText(self::EL_EMAIL, _('E-mail'));
        $email->addCondition(Form::FILLED)
                ->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'));

        $createLogin = $container->addCheckbox(self::EL_CREATE_LOGIN, _('Vytvořit login'))
                ->setOption('description', _('Vytvoří login a pošle e-mail s instrukcemi pro první přihlášení.'));
        $email->addConditionOn($createLogin, Form::FILLED)
                ->addRule(Form::FILLED, _('Pro vytvoření loginu je třeba zadat e-mail.'));

        $container->addSelect(self::EL_CREATE_LOGIN_LANG, _('Jazyk pozvánky'))
                ->setItems($this->translator->getSupportedLanguages())
                ->setDefaultValue($this->translator->getLang());

        return $container;
    }

    public final function modifyLoginContainer(Form $form, ModelPerson $person) {
        $container = $form->getComponent(self::CONT_LOGIN);
        $login = $person->getLogin();
        $personInfo = $person->getInfo();
        $hasEmail = ($login && isset($login->email)) || ($personInfo && isset($personInfo->email));
        $showLogin = !$login || !$hasEmail;
        if (!$showLogin) {
            foreach ($container->getControls() as $control) {
                $control->setDisabled();
            }
        }
        if ($login) {
            $container[self::EL_CREATE_LOGIN]->setDefaultValue(true);
            $container[self::EL_CREATE_LOGIN]->setDisabled();
        }

        $email = ($login && isset($login->email)) ? $login->email :
                ($personInfo && isset($personInfo->email)) ? $personInfo->email :
                        null;
        $container[self::EL_EMAIL]->setDefaultValue($email);


        $emailRule = $this->uniqueEmailFactory->create($person);
        //$form[ContestantWizardFactory::CONT_PERSON_INFO]['email']->addCondition(Form::FILLED)->addRule($emailRule, _('Daný e-mail již někdo používá.'));
        $form[self::CONT_LOGIN][self::EL_EMAIL]->addRule($emailRule, _('Daný e-mail již někdo používá.'));
    }

}

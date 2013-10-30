<?php

namespace PublicModule;

use BasePresenter as CoreBasePresenter;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\ContestantFactory;
use FKSDB\Components\Forms\Factories\LoginFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Rules\UniqueEmail;
use FKSDB\Components\Forms\Rules\UniqueEmailFactory;
use FKSDB\Components\Forms\Rules\UniqueLoginFactory;
use FormUtils;
use IContestPresenter;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use ModelContest;
use ModelContestant;
use ModelException;
use ModelPerson;
use ModelPostContact;
use Nette\Application\UI\Form;
use Nette\Database\Connection;
use Nette\DateTime;
use Nette\Diagnostics\Debugger;
use ServiceAddress;
use ServiceContestant;
use ServiceLogin;
use ServiceMPostContact;
use ServicePerson;
use ServicePersonInfo;
use ServicePostContact;

/**
 * INPUT:
 *   contest (nullable)
 *   logged user (nullable)
 *   condition: the logged user is not contestant of the contest
 *   condition: the logged user is a person
 * 
 * OUTPUT:
 *   registered contestant for the current year
 *      - if contest was provided in that contest
 *      - if user was provided for that user
 * 
 * OPERATION
 *   - show/process person/login info iff logged user is null
 *   - show contest selector iff contest is null
 *   - contestant for filling default values
 *     - user must be logged in
 *     - if exists use last contestant from the provided contest
 *     - otherwise use last contestant from any contest (Vyfuk <= FYKOS)
 * 
 * Just proof of concept.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class RegisterPresenter extends CoreBasePresenter implements IContestPresenter {

    const CONT_PERSON = 'person';
    const CONT_PERSON_INFO = 'person_info';
    const CONT_LOGIN = 'login';
    const CONT_ADDRESS = 'address';
    const CONT_CONTESTANT = 'contestant';

    /**
     * @var int
     * @persistent
     */
    public $contestId;

    /** @var ServicePerson */
    private $servicePerson;

    /** @var ServicePersonInfo */
    private $servicePersonInfo;

    /** @var ServiceLogin */
    private $serviceLogin;

    /** @var ServiceContestant */
    private $serviceContestant;

    /** @var ServiceAddress */
    private $serviceAddress;

    /** @var ServicePostContact */
    private $servicePostContact;

    /** @var ServiceMPostContact */
    private $serviceMPostContact;

    /** @var LoginFactory */
    private $loginFactory;

    /** @var PersonFactory */
    private $personFactory;

    /** @var AddressFactory */
    private $addressFactory;

    /** @var ContestantFactory */
    private $contestantFactory;

    /** @var UniqueEmailFactory */
    private $uniqueEmailFactory;

    /** @var UniqueLoginFactory */
    private $uniqueLoginFactory;

    /** @var Connection */
    private $connection;

    public function injectLoginFactory(LoginFactory $loginFactory) {
        $this->loginFactory = $loginFactory;
    }

    public function injectPersonFactory(PersonFactory $personFactory) {
        $this->personFactory = $personFactory;
    }

    public function injectAddressFactory(AddressFactory $addressFactory) {
        $this->addressFactory = $addressFactory;
    }

    public function injectContestantFactory(ContestantFactory $contestantFactory) {
        $this->contestantFactory = $contestantFactory;
    }

    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    public function injectServicePersonInfo(ServicePersonInfo $servicePersonInfo) {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    public function injectServiceLogin(ServiceLogin $serviceLogin) {
        $this->serviceLogin = $serviceLogin;
    }

    public function injectServiceContestant(ServiceContestant $serviceContestant) {
        $this->serviceContestant = $serviceContestant;
    }

    public function injectServiceAddress(ServiceAddress $serviceAddress) {
        $this->serviceAddress = $serviceAddress;
    }

    public function injectServicePostContact(ServicePostContact $servicePostContact) {
        $this->servicePostContact = $servicePostContact;
    }

    public function injectServiceMPostContact(ServiceMPostContact $serviceMPostContact) {
        $this->serviceMPostContact = $serviceMPostContact;
    }

    public function injectUniqueEmailFactory(UniqueEmailFactory $uniqueEmailFactory) {
        $this->uniqueEmailFactory = $uniqueEmailFactory;
    }

    public function injectUniqueLoginFactory(UniqueLoginFactory $uniqueLoginFactory) {
        $this->uniqueLoginFactory = $uniqueLoginFactory;
    }

    public function injectConnection(Connection $connection) {
        $this->connection = $connection;
    }

    /** @var ModelContest|null */
    private $selectedContest;

    public function getSelectedContest() {
        if ($this->selectedContest === null) {
            $this->selectedContest = $this->serviceContest->findByPrimary($this->contestId);
        }
        return $this->selectedContest;
    }

    public function getSelectedYear() {
        return $this->yearCalculator->getCurrentYear($this->getSelectedContest());
    }

    public function actionDefault() {
        // so far we do not implement registration of person only
        $this->redirect('contestant');
    }

    public function actionContestant() {
        if ($this->user->isLoggedIn()) {
            $person = $this->user->getIdentity()->getPerson();
            if (!$person) {
                $this->flashMessage('Uživatel musí být osobou, aby se mohl registrovat jako řešitel.', self::FLASH_INFO);
                $this->redirect(':Authentication:login');
            }

            if ($this->getSelectedContest()) {
                $contestnats = $person->getActiveContestants($this->yearCalculator);
                $contest = $this->getSelectedContest();
                if (isset($contestnats[$contest->contest_id])) {
                    $this->flashMessage(sprintf('%s již řeší %s.', $person->getFullname(), $contest->name), self::FLASH_INFO);
                    $this->redirect(':Public:Dashboard:default');
                }
            }
        }
    }

    public function titleContestant() {
        $this->setTitle(_('Registrace řešitele'));
    }

    public function renderContestant() {
        $person = $this->user->isLoggedIn() ? $this->user->getIdentity()->getPerson() : null;
        if (!$person) {
            return; // we have now default values for anonymous user
        }

        $defaults = array();


        $defaults[self::CONT_PERSON] = $person;

        $address = $person->getDeliveryAddress();
        $defaults[self::CONT_ADDRESS] = $address ? : array();

        $contestant = $this->getBaseContestant($person);
        $defaults[self::CONT_CONTESTANT] = $contestant ? : array();

        $personInfo = $person->getInfo();
        $defaults[self::CONT_PERSON_INFO] = $personInfo ? : array();

        $this['contestantForm']->setDefaults($defaults);
    }

    public function createComponentContestantForm($name) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());

        $person = $this->user->isLoggedIn() ? $this->user->getIdentity()->getPerson() : null;

        /*
         * Login
         */
        if (!$person) {
            $group = $form->addGroup('Přihlašování');
            $emailRule = $this->uniqueEmailFactory->create(UniqueEmail::CHECK_LOGIN);
            $loginRule = $this->uniqueLoginFactory->create();
            $login = $this->loginFactory->createLogin(LoginFactory::SHOW_PASSWORD | LoginFactory::REQUIRE_PASSWORD, $group, $emailRule, $loginRule);
            $form->addComponent($login, self::CONT_LOGIN);
        }

        /*
         * Person
         */
        if ($person) {
            $personFlags = PersonFactory::DISABLED;
        } else {
            $personFlags = 0;
        }
        $group = $form->addGroup('Osoba');
        $personCont = $this->personFactory->createPerson($personFlags, $group);
        $form->addComponent($personCont, self::CONT_PERSON);

        $address = $this->addressFactory->createAddress($group);
        $form->addComponent($address, self::CONT_ADDRESS);

        if ($person) {
            $needInfo = !$person->getInfo() || !$person->getInfo()->agreed || !$person->getInfo()->origin;
        } else {
            $needInfo = true;
        }
        if ($needInfo) {
            $personInfo = $this->personFactory->createPersonInfo(PersonFactory::SHOW_LIKE_SUPPLEMENT | PersonFactory::REQUIRE_AGREEMENT, $group);
            $form->addComponent($personInfo, self::CONT_PERSON_INFO);
        }

        /*
         * Contestant
         */
        $group = $form->addGroup('Řešitel');
        $options = ContestantFactory::REQUIRE_SCHOOL | ContestantFactory::REQUIRE_STUDY_YEAR;
        if (!$this->getSelectedContest()) {
            $options |= ContestantFactory::SHOW_CONTEST;
        }
        $contestant = $this->contestantFactory->createContestant($options, $group);
        $form->addComponent($contestant, self::CONT_CONTESTANT);

        /*
         * Buttons
         */
        $form->setCurrentGroup();
        $form->addSubmit('register', 'Registrovat');
        $form->onSuccess[] = array($this, 'handleContestantFormSuccess');

        $form->addProtection('Vypršela časová platnost formuláře. Odešlete jej prosím znovu.');

        return $form;
    }

    public function handleContestantFormSuccess(Form $form) {
        $values = $form->getValues();
        $loggedPerson = $this->user->isLoggedIn() ? $this->user->getIdentity()->getPerson() : null;

        try {
            if (!$this->connection->beginTransaction()) {
                throw new ModelException();
            }

            /*
             * Person and login
             */
            if (!$loggedPerson) {
                // store person
                $personData = $values[self::CONT_PERSON];
                $person = $this->servicePerson->createNew($personData);
                $person->inferGender();
                $this->servicePerson->save($person);

                // store login
                $loginData = $values[self::CONT_LOGIN];
                $loginData = FormUtils::emptyStrToNull($loginData);
                $login = $this->serviceLogin->createNew($loginData);
                $login->person_id = $person->person_id;

                $this->serviceLogin->save($login); // save to retrieve login_id for hash salting

                $login->setHash($loginData['password']);
                $login->active = 1; // created accounts are active
                $this->serviceLogin->save($login);
            } else {
                $person = $loggedPerson;
            }

            /*
             * Address
             */
            foreach ($person->getMPostContacts() as $mPostContact) {
                $this->serviceMPostContact->dispose($mPostContact);
            }

            // store address
            $dataPostContact = $values[self::CONT_ADDRESS];
            $dataPostContact = FormUtils::emptyStrToNull((array) $dataPostContact);
            $mPostContact = $this->serviceMPostContact->createNew($dataPostContact);
            $mPostContact->getPostContact()->person_id = $person->person_id;
            $mPostContact->getPostContact()->type = ModelPostContact::TYPE_PERMANENT;

            $this->serviceMPostContact->save($mPostContact);

            /*
             * Contestant
             */
            $contestantData = $values[self::CONT_CONTESTANT];
            $contestantData = FormUtils::emptyStrToNull($contestantData);
            $contestant = $this->serviceContestant->createNew($contestantData);

            $contestant->person_id = $person->person_id;
            if (isset($contestant->contest_id)) {
                $contest = $this->serviceContest->findByPrimary($contestant->contest_id); //TODO try calling ORMs ref
                $contestant->year = $this->yearCalculator->getCurrentYear($contest);
            } else {
                $contestant->year = $this->getSelectedYear();
                $contestant->contest_id = $this->getSelectedContest()->contest_id;
            }

            $this->serviceContestant->save($contestant);

            /*
             * Person info
             */
            if (isset($values[self::CONT_PERSON_INFO])) { // depends on needInfo
                $personInfoData = $values[self::CONT_PERSON_INFO];
                $personInfoData = FormUtils::emptyStrToNull($personInfoData);
                $personInfoData['agreed'] = $personInfoData['agreed'] ? new DateTime() : null;
                $personInfo = $person->getInfo();
                if (!$personInfo) {
                    $personInfo = $this->servicePersonInfo->createNew($personInfoData);
                    $personInfo->person_id = $person->person_id;
                } else {
                    $this->servicePersonInfo->updateModel($personInfo, $personInfoData); // here we update date of the confirmation
                }

                $this->servicePersonInfo->save($personInfo);
            }

            if (!$this->connection->commit()) {
                throw new ModelException();
            }

            if (!$loggedPerson) {
                $this->getUser()->login($login);
            }

            $this->flashMessage($person->gender == 'F' ? 'Řešitelka úspěšně zaregistrována.' : 'Řešitel úspěšně zaregistrován.', self::FLASH_SUCCESS);
            $this->redirect(':Public:Dashboard:default');
        } catch (ModelException $e) {
            $this->connection->rollBack();
            $this->getUser()->logout(true);
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage('Při registraci došlo k chybě.', self::FLASH_ERROR);
        }
    }

    private function getBaseContestant(ModelPerson $person) {
        $contestant = null;
        if ($this->getSelectedContest()) {
            $contestant = $person->getLastContestant($this->getSelectedContest());
        }

        if (!$contestant) {
            $contestant = $person->getContestants()->order('contest_id DESC')->fetch();
            $contestant = $contestant ? ModelContestant::createFromTableRow($contestant) : null;
        }

        return $contestant;
    }

}

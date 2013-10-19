<?php

namespace PublicModule;

use BasePresenter;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\ContestantFactory;
use FKSDB\Components\Forms\Factories\LoginFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Rules\UniqueEmail;
use FKSDB\Components\Forms\Rules\UniqueEmailFactory;
use FKSDB\Components\Forms\Rules\UniqueLoginFactory;
use FormUtils;
use IContestPresenter;
use ModelContest;
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
 * Just proof of concept.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class RegisterPresenter extends BasePresenter implements IContestPresenter {

    const CONT_PERSON = 'person';
    const CONT_PERSON_INFO = 'person_info';
    const CONT_LOGIN = 'login';
    const CONT_ADDRESS = 'address';
    const CONT_CONTESTANT = 'contestant';

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

    /** @var ModelContest */
    private $selectedContest;

    public function getSelectedContest() {
        if ($this->selectedContest === null) {
            $this->selectedContest = $this->serviceContest->findByPrimary(ModelContest::ID_FYKOS);
        }
        return $this->selectedContest;
    }

    public function getSelectedYear() {
        return $this->yearCalculator->getCurrentYear($this->getSelectedContest());
    }

    public function actionDefault() {
        if ($this->user->isLoggedIn()) {
            /** @var ModelPerson $person */
            $person = $this->user->getIdentity()->getPerson();
            $currentContestants = $person->getContestants()
                    ->where('contest_id = ?', $this->getSelectedContest()->contest_id)
                    ->where('year = ?', $this->getSelectedYear());

            if (count($currentContestants) > 0) {
                // existing contestant 
                $this->redirect(':Public:Dashboard:default');
            } else {
                // only registered person 
                $this->redirect('contestant');
            }
        }
    }

    public function actionContestant() {
        if ($this->user->isLoggedIn()) {
            /** @var ModelPerson $person */
            $person = $this->user->getIdentity()->getPerson();
            $currentContestants = $person->getContestants()
                    ->where('contest_id = ?', $this->getSelectedContest()->contest_id)
                    ->where('year = ?', $this->getSelectedYear());

            if (count($currentContestants) > 0) {
                // existing contestant 
                $this->redirect(':Public:Dashboard:default');
            }
        } else {
            // not logged in
            $this->redirect('default');
        }
    }

    public function createComponentRegisterForm($name) {
        $form = new Form();

        $group = $form->addGroup('Přihlašování');
        $emailRule = $this->uniqueEmailFactory->create(UniqueEmail::CHECK_LOGIN);
        $loginRule = $this->uniqueLoginFactory->create();
        $login = $this->loginFactory->createLogin(LoginFactory::SHOW_PASSWORD | LoginFactory::REQUIRE_PASSWORD, $group, $emailRule, $loginRule);
        $form->addComponent($login, self::CONT_LOGIN);

        $group = $form->addGroup('Osoba');
        $person = $this->personFactory->createPerson(0, $group);
        $form->addComponent($person, self::CONT_PERSON);

        $address = $this->addressFactory->createAddress($group);
        $form->addComponent($address, self::CONT_ADDRESS);

        $personInfo = $this->personFactory->createPersonInfo(PersonFactory::SHOW_LIKE_SUPPLEMENT | PersonFactory::REQUIRE_AGREEMENT, $group);
        $form->addComponent($personInfo, self::CONT_PERSON_INFO);

        $group = $form->addGroup('Řešitel');
        $contestant = $this->contestantFactory->createContestant(ContestantFactory::REQUIRE_SCHOOL | ContestantFactory::REQUIRE_STUDY_YEAR, $group);
        $form->addComponent($contestant, self::CONT_CONTESTANT);


        $form->setCurrentGroup();
        $form->addSubmit('register', 'Registrovat');
        $form->onSuccess[] = array($this, 'handleRegisterFormSuccess');


        return $form;
    }

    public function createComponentContestantForm($name) {
        $form = new Form();

        // person
        $person = $this->user->getIdentity()->getPerson();
        $group = $form->addGroup('Osoba');
        $personContainer = $this->personFactory->createPerson(PersonFactory::DISABLED, $group);
        $personContainer->setDefaults($person->toArray());
        $form->addComponent($personContainer, self::CONT_PERSON);

        // contestant
        $contestant = $person->getLastContestant($this->getSelectedContest());
        $group = $form->addGroup('Řešitel');
        $contestantContainer = $this->contestantFactory->createContestant(ContestantFactory::REQUIRE_SCHOOL | ContestantFactory::REQUIRE_STUDY_YEAR, $group);
        if ($contestant) {
            $contestantContainer->setDefaults($contestant); //TODO auto-increase study_year + class
        }
        $form->addComponent($contestantContainer, self::CONT_CONTESTANT);

        // address
        $address = $person->getDeliveryAddress();
        $group = $form->addGroup('Adresa');
        $addressContainer = $this->addressFactory->createAddress($group);
        if ($address) {
            $addressContainer->setDefaults($address);
        }
        $form->addComponent($addressContainer, self::CONT_ADDRESS);

        // person info
        $personInfo = $person->getInfo();
        if (!$personInfo || (!$personInfo->agreed || !$personInfo->origin)) {
            $group = $form->addGroup('Informace');
            $personInfoContainer = $this->personFactory->createPersonInfo(PersonFactory::SHOW_LIKE_SUPPLEMENT | PersonFactory::REQUIRE_AGREEMENT, $group);
            if ($personInfo) {
                $personInfoContainer->setDefaults($personInfo);
            }
            $form->addComponent($personInfoContainer, self::CONT_PERSON_INFO);
        }


        $form->setCurrentGroup();
        $form->addSubmit('register', 'Registrovat');
        $form->onSuccess[] = array($this, 'handleContestantFormSuccess');

        $form->addProtection('Vypršela časová platnost formuláře. Odešlete jej prosím znovu.');

        return $form;
    }

    public function handleRegisterFormSuccess(Form $form) {
        $values = $form->getValues();

        try {
            if (!$this->connection->beginTransaction()) {
                throw new ModelException();
            }
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

            // store address
            $addressData = $values[self::CONT_ADDRESS];
            $addressData = FormUtils::emptyStrToNull($addressData);
            $mPostContact = $this->serviceMPostContact->createNew($addressData);
            $mPostContact->getJoinedModel()->person_id = $person->person_id;

            if (!$mPostContact->getMainModel()->inferRegion()) {
                //TODO nebo do logu?
                $this->flashMessage(sprintf('Nezdařilo se přiřadit region dle PSČ %s.', $mPostContact->getMainModel()->postal_code));
            }

            $this->serviceMPostContact->save($mPostContact);

            // store contestant
            $contestantData = $values[self::CONT_CONTESTANT];
            $contestantData = FormUtils::emptyStrToNull($contestantData);
            $contestant = $this->serviceContestant->createNew($contestantData);

            $contestant->person_id = $person->person_id;
            $contestant->year = $this->getSelectedYear();
            $contestant->contest_id = $this->getSelectedContest()->contest_id;

            $this->serviceContestant->save($contestant);

            // store person info
            $personInfoData = $values[self::CONT_PERSON_INFO];
            $personInfoData = FormUtils::emptyStrToNull($personInfoData);
            $personInfoData['agreed'] = $personInfoData['agreed'] ? new DateTime() : null;
            $personInfo = $this->servicePersonInfo->createNew($personInfoData);

            $personInfo->person_id = $person->person_id;

            $this->servicePersonInfo->save($personInfo);


            if (!$this->connection->commit()) {
                throw new ModelException();
            }

            $this->flashMessage($person->gender == 'F' ? 'Řešitelka úspěšně zaregistrována.' : 'Řešitel úspěšně zaregistrován.');
            $this->redirect(':Public:Dashboard:default');
        } catch (ModelException $e) {
            $this->connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage('Při registraci došlo k chybě.', 'error');
        }
    }

    public function handleContestantFormSuccess(Form $form) {
        $values = $form->getValues();

        try {
            if (!$this->connection->beginTransaction()) {
                throw new ModelException();
            }
            $person = $this->user->getIdentity()->getPerson();

            /*
             * Contestant
             */
            $contestantData = $values[self::CONT_CONTESTANT];
            $contestantData = FormUtils::emptyStrToNull($contestantData);
            $contestant = $this->serviceContestant->createNew($contestantData);

            $contestant->person_id = $person->person_id;
            $contestant->year = $this->getSelectedYear();
            $contestant->contest_id = $this->getSelectedContest()->contest_id;

            $this->serviceContestant->save($contestant);

            /*
             * Address
             * TODO allow multiple addresses, not hardcode type of the post contact
             */
            foreach ($person->getMPostContacts() as $mPostContact) {
                $this->serviceMPostContact->dispose($mPostContact);
            }

            $dataPostContact = $values[self::CONT_ADDRESS];
            $dataPostContact = FormUtils::emptyStrToNull((array) $dataPostContact);
            $mPostContact = $this->serviceMPostContact->createNew($dataPostContact);
            $mPostContact->getPostContact()->person_id = $person->person_id;
            $mPostContact->getPostContact()->type = ModelPostContact::TYPE_PERMANENT;

            $this->serviceMPostContact->save($mPostContact);

            /*
             * Person info
             */
            $dataInfo = $values[self::CONT_PERSON_INFO];
            $dataInfo = FormUtils::emptyStrToNull($dataInfo);
            $dataInfo['agreed'] = $dataInfo['agreed'] ? new DateTime() : null;
            $personInfo = $person->getInfo();
            if (!$personInfo) {
                $personInfo = $this->servicePersonInfo->createNew($dataInfo);
                $personInfo->person_id = $person->person_id;
            } else {
                $this->servicePersonInfo->updateModel($personInfo, $dataInfo); // here we update date of the confirmation
            }

            $this->servicePersonInfo->save($personInfo);


            if (!$this->connection->commit()) {
                throw new ModelException();
            }

            $this->flashMessage($person->gender == 'F' ? 'Řešitelka úspěšně zaregistrována.' : 'Řešitel úspěšně zaregistrován.');
            $this->redirect(':Public:Dashboard:default');
        } catch (ModelException $e) {
            $this->connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage('Při registraci došlo k chybě.', 'error');
        }
    }

}

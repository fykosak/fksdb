<?php

namespace OrgModule;

use AbstractModelSingle;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Rules\UniqueEmail;
use FKSDB\Components\Forms\Rules\UniqueEmailFactory;
use FKSDB\Components\WizardComponent;
use FormUtils;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Kdyby\Extension\Forms\Replicator\Replicator;
use ModelException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\SubmitButton;
use Nette\NotImplementedException;
use ServiceLogin;
use ServiceMPostContact;
use ServicePerson;
use ServicePersonInfo;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonPresenter extends EntityPresenter {

    /**
     * @persistent
     * @var string
     */
    public $backlink = '';

    const CONT_PERSON = 'person';
    const CONT_ADDRESSES = 'addresses';
    const CONT_PERSON_INFO = 'personInfo';

    protected $modelResourceId = 'person';

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var ServicePersonInfo
     */
    private $servicePersonInfo;

    /**
     * @var ServiceLogin
     */
    private $serviceLogin;

    /**
     * @var ServiceMPostContact
     */
    private $serviceMPostContact;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var UniqueEmailFactory
     */
    private $uniqueEmailFactory;

    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    public function injectServicePersonInfo(ServicePersonInfo $servicePersonInfo) {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    public function injectServiceLogin(ServiceLogin $serviceLogin) {
        $this->serviceLogin = $serviceLogin;
    }

    public function injectServiceMPostContact(ServiceMPostContact $serviceMPostContact) {
        $this->serviceMPostContact = $serviceMPostContact;
    }

    public function injectAddressFactory(AddressFactory $addressFactory) {
        $this->addressFactory = $addressFactory;
    }

    public function injectPersonFactory(PersonFactory $personFactory) {
        $this->personFactory = $personFactory;
    }

    public function injectUniqueEmailFactory(UniqueEmailFactory $uniqueEmailFactory) {
        $this->uniqueEmailFactory = $uniqueEmailFactory;
    }

    protected function createComponentCreateComponent($name) {
        // So far, there's no use case that creates bare person.
        throw new NotImplementedException();
    }

    protected function createComponentEditComponent($name) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        
        $person = $this->getModel();

        /*
         * Person
         */
        $group = $form->addGroup('Osoba');
        $personContainer = $this->personFactory->createPerson(PersonFactory::SHOW_DISPLAY_NAME | PersonFactory::SHOW_GENDER, $group);
        $form->addComponent($personContainer, self::CONT_PERSON);

        /**
         * Addresses
         */
        $group = $form->addGroup('Adresy');
        $factory = $this->addressFactory;
        $replicator = new Replicator(function($replContainer) use($factory, $group) {
                    $factory->buildAddress($replContainer, $group);
                    $replContainer->addComponent($factory->createTypeElement(), 'type');

                    $replContainer->addSubmit('remove', 'Odebrat adresu')->addRemoveOnClick();
                }, 1, true);
        $replicator->containerClass = 'FKSDB\Components\Forms\Containers\AddressContainer';

        $form->addComponent($replicator, self::CONT_ADDRESSES);

        $replicator->addSubmit('add', 'Přidat adresu')->addCreateOnClick();


        /**
         * Personal information
         */
        $group = $form->addGroup('Osobní informace');
        $login = $this->getModel()->getLogin();
        if ($login) {
            $rule = $this->uniqueEmailFactory->create(UniqueEmail::CHECK_LOGIN, null, $login);
        } else {
            $rule = $this->uniqueEmailFactory->create(UniqueEmail::CHECK_PERSON, $this->getModel(), null);
        }

        $infoContainer = $this->personFactory->createPersonInfo(PersonFactory::SHOW_EMAIL, $group, $rule);
        $form->addComponent($infoContainer, self::CONT_PERSON_INFO);

        $form->setCurrentGroup();

        $form->addSubmit('send', 'Uložit')->onClick[] = array($this, 'handleEditFormSuccess');

        return $form;
    }

    protected function setDefaults(AbstractModelSingle $person, Form $form) {
        $defaults = array();

        $defaults[self::CONT_PERSON] = $person;

        $addresses = array();
        foreach ($person->getMPostContacts() as $mPostContact) {
            $addresses[] = $mPostContact;
        }
        $defaults[self::CONT_ADDRESSES] = $addresses;

        $info = $person->getInfo();
        if ($info) {
            $defaults[self::CONT_PERSON_INFO] = $info;
        }

        $login = $person->getLogin();
        if ($login) {
            $defaults[self::CONT_PERSON_INFO]['email'] = $login->email;
        }

        $form->setDefaults($defaults);
    }

    /**
     * @internal
     * @param WizardComponent $wizard
     * @throws ModelException
     */
    public function handleEditFormSuccess(SubmitButton $button) {
        $form = $button->getForm();
        $connection = $this->servicePerson->getConnection();
        $values = $form->getValues();
        $person = $this->getModel();

        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }

            /*
             * Person
             */
            $dataPerson = FormUtils::emptyStrToNull($values[self::CONT_PERSON]);
            $this->servicePerson->updateModel($person, $dataPerson);
            $this->servicePerson->save($person);



            /*
             * Post contacts
             */
            foreach ($person->getMPostContacts() as $mPostContact) {
                $this->serviceMPostContact->dispose($mPostContact);
            }

            $dataPostContacts = $values[self::CONT_ADDRESSES];
            foreach ($dataPostContacts as $dataPostContact) {
                $dataPostContact = FormUtils::emptyStrToNull((array) $dataPostContact);
                $mPostContact = $this->serviceMPostContact->createNew($dataPostContact);
                $mPostContact->getPostContact()->person_id = $person->person_id;

                $this->serviceMPostContact->save($mPostContact);
            }


            /*
             * Email stored both in login and person_info
             */
            $dataInfo = $values[self::CONT_PERSON_INFO];
            $dataInfo = FormUtils::emptyStrToNull($dataInfo);
            $email = $dataInfo['email'];
            if ($email) {
                unset($dataInfo['email']);
                $login = $person->getLogin();
                if ($login) {
                    $login->email = $email;
                    $this->serviceLogin->save($login);
                } else {
                    $dataInfo['email'] = $email; // we'll store it as personal info
                }
            } else {
                $dataInfo['email'] = null; // erase the person_info field
            }

            /*
             * Personal info
             */
            $personInfo = $person->getInfo();
            if (!$personInfo) {
                $personInfo = $this->servicePersonInfo->createNew($dataInfo);
                $personInfo->person_id = $person->person_id;
            } else {
                unset($dataInfo['agreed']); // not to overwrite existing confirmation
                $this->servicePersonInfo->updateModel($personInfo, $dataInfo);
            }

            $this->servicePersonInfo->save($personInfo);

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new ModelException();
            }

            $this->flashMessage(sprintf('Údaje osoby %s upraveny.', $person->getFullname()), self::FLASH_SUCCESS);

            $this->restoreRequest($this->backlink);
            $this->redirect('list');
        } catch (ModelException $e) {
            $connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage('Chyba při úpravě řešitele.', self::FLASH_ERROR);
        }
    }

    protected function createComponentGrid($name) {
        // So far, there's no use case that would list all persons.
        throw new NotImplementedException();
    }

    protected function createModel($id) {
        return $this->servicePerson->findByPrimary($id);
    }

}

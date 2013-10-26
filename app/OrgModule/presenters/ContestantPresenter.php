<?php

namespace OrgModule;

use AbstractModelSingle;
use FKSDB\Components\Factories\ContestantWizardFactory;
use FKSDB\Components\Forms\Factories\ContestantFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Rules\UniqueEmail;
use FKSDB\Components\Forms\Rules\UniqueEmailFactory;
use FKSDB\Components\Grids\ContestantsGrid;
use FKSDB\Components\WizardComponent;
use FormUtils;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use MailNotSendException;
use MailTemplateFactory;
use ModelException;
use ModelPerson;
use Nette\Application\UI\Form;
use Nette\DateTime;
use Nette\Diagnostics\Debugger;
use ServiceContestant;
use ServiceLogin;
use ServiceMPostContact;
use ServicePerson;
use ServicePersonInfo;

class ContestantPresenter extends EntityPresenter {

    const CONT_PERSON = 'person';
    const CONT_CONTESTANT = 'contestant';

    protected $modelResourceId = 'contestant';

    /**
     * @var ServiceContestant
     */
    private $serviceContestant;

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
     * @var ContestantFactory
     */
    private $contestantFactory;

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var ContestantWizardFactory
     */
    private $contestantWizardFactory;

    /**
     * @var UniqueEmailFactory
     */
    private $uniqueEmailFactory;

    /**
     * @var MailTemplateFactory
     */
    private $mailTemplateFactory;

    public function injectServiceContestant(ServiceContestant $serviceContestant) {
        $this->serviceContestant = $serviceContestant;
    }

    public function injectContestantWizardFactory(ContestantWizardFactory $contestantWizardFactory) {
        $this->contestantWizardFactory = $contestantWizardFactory;
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

    public function injectServiceMPostContact(ServiceMPostContact $serviceMPostContact) {
        $this->serviceMPostContact = $serviceMPostContact;
    }

    public function injectContestantFactory(ContestantFactory $contestantFactory) {
        $this->contestantFactory = $contestantFactory;
    }

    public function injectPersonFactory(PersonFactory $personFactory) {
        $this->personFactory = $personFactory;
    }

    public function injectUniqueEmailFactory(UniqueEmailFactory $uniqueEmailFactory) {
        $this->uniqueEmailFactory = $uniqueEmailFactory;
    }

    public function injectMailTemplateFactory(MailTemplateFactory $mailTemplateFactory) {
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    public function renderEdit($id) {
        parent::renderEdit($id);

        $contestant = $this->getModel();

        if ($contestant->contest_id != $this->getSelectedContest()->contest_id) {
            $this->flashMessage('Editace řešitele mimo zvolený seminář.', self::FLASH_WARNING);
        }

        if ($contestant->year != $this->getSelectedYear()) {
            $this->flashMessage('Editace řešitele mimo zvolený ročník semináře.', self::FLASH_WARNING);
        }
    }

    protected function setDefaults(AbstractModelSingle $model, Form $form) {
        $form[self::CONT_PERSON]->setValues($this->getModel()->getPerson()->toArray());
        $form[self::CONT_CONTESTANT]->setDefaults($this->getModel()->toArray());
    }

    protected function createComponentCreateComponent($name) {
        $wizard = $this->contestantWizardFactory->create();

        $wizard->onProcess[] = array($this, 'processWizard');
        $wizard->onStepInit[] = array($this, 'initWizard');

        return $wizard;
    }

    protected function createComponentGrid($name) {
        $grid = new ContestantsGrid($this->serviceContestant);

        return $grid;
    }

    protected function createComponentEditComponent($name) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        
        $personContainer = $this->personFactory->createPerson(PersonFactory::DISABLED);
        $form->addComponent($personContainer, self::CONT_PERSON);

        $contestantContainer = $this->contestantFactory->createContestant();
        $form->addComponent($contestantContainer, self::CONT_CONTESTANT);

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = array($this, 'handleContestantEditFormSuccess');

        return $form;
    }

    /**
     * @internal
     * @param WizardComponent $wizard
     * @throws ModelException
     */
    public function processWizard(WizardComponent $wizard) {
        $connection = $this->servicePerson->getConnection();
        $personData = $wizard->getData(ContestantWizardFactory::STEP_PERSON);
        $person = $this->getPersonFromPersonStep($personData);

        /*
         * Finish validation
         */
        $dataForm = $wizard->getComponent(ContestantWizardFactory::STEP_DATA);
        $dataFormValues = $dataForm->getValues();
        $personInfoFormValues = $dataFormValues[ContestantWizardFactory::CONT_PERSON_INFO];
        $login = $person->getLogin();
        $emailRule = null;
        if ($login) {
            $emailRule = $this->uniqueEmailFactory->create(UniqueEmail::CHECK_LOGIN, null, $login);
        } else if ($personInfoFormValues['email']) {
            if ($personInfoFormValues[PersonFactory::EL_CREATE_LOGIN]) {
                $emailRule = $this->uniqueEmailFactory->create(UniqueEmail::CHECK_LOGIN);
            } else {
                $emailRule = $this->uniqueEmailFactory->create(UniqueEmail::CHECK_PERSON, $person);
            }
        }
        if ($emailRule) {
            if (!$emailRule->__invoke($dataForm[ContestantWizardFactory::CONT_PERSON_INFO]['email'])) {
                $dataForm->addError('Daný e-mail již někdo používá.');
                return;
            }
        }

        /*
         * Process data
         */
        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }

            /*
             * Person
             */
            $this->servicePerson->save($person);

            /*
             * Contestant
             */
            $data = $wizard->getData(ContestantWizardFactory::STEP_DATA);

            $dataContestant = $data[ContestantWizardFactory::CONT_CONTESTANT];
            $dataContestant = FormUtils::emptyStrToNull($dataContestant);

            $contestant = $this->serviceContestant->createNew($dataContestant);

            $contestant->person_id = $person->person_id;
            $contestant->contest_id = $this->getSelectedContest()->contest_id;
            $contestant->year = $this->getSelectedYear();

            $this->serviceContestant->save($contestant);


            /*
             * Post contacts
             */
            foreach ($person->getMPostContacts() as $mPostContact) {
                $this->serviceMPostContact->dispose($mPostContact);
            }

            $dataPostContacts = $data[ContestantWizardFactory::CONT_ADDRESSES];
            foreach ($dataPostContacts as $dataPostContact) {
                $dataPostContact = FormUtils::emptyStrToNull((array) $dataPostContact);
                $mPostContact = $this->serviceMPostContact->createNew($dataPostContact);
                $mPostContact->getPostContact()->person_id = $person->person_id;

                $this->serviceMPostContact->save($mPostContact);
            }


            /*
             * Login
             */
            $dataInfo = $data[ContestantWizardFactory::CONT_PERSON_INFO];
            $dataInfo = FormUtils::emptyStrToNull($dataInfo);
            $email = $dataInfo['email'];
            if ($email) {
                unset($dataInfo['email']);
                $login = $person->getLogin();
                if ($login) {
                    $login->email = $email;
                    $this->serviceLogin->save($login);
                } else if ($dataInfo[PersonFactory::EL_CREATE_LOGIN]) {
                    $template = $this->mailTemplateFactory->createLoginInvitation($this, 'cs'); //TODO i18n of created logins
                    try {
                        $login = $this->serviceLogin->createLoginWithInvitation($template, $person, $email);
                        $this->flashMessage('Zvací e-mail odeslán.', self::FLASH_INFO);
                    } catch (MailNotSendException $e) {
                        $this->flashMessage('Zvací e-mail se nepodařilo odeslat.', self::FLASH_ERROR);
                    }
                } else {
                    $dataInfo['email'] = $email; // we'll store it as personal info
                }
            }

            /*
             * Personal info
             */
            $personInfo = $person->getInfo();
            if (!$personInfo) {
                $dataInfo['agreed'] = $dataInfo['agreed'] ? new DateTime() : null;
                $personInfo = $this->servicePersonInfo->createNew($dataInfo);                
                $personInfo->person_id = $person->person_id;
            } else {
                unset($dataInfo['agreed']); // do not overwrite in existing person_info
                $this->servicePersonInfo->updateModel($personInfo, $dataInfo);
            }
            $this->servicePersonInfo->save($personInfo);

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new ModelException();
            }
            $wizard->disposeData();

            $this->flashMessage(sprintf('Řešitel %s založen.', $person->getFullname()), self::FLASH_SUCCESS);
            $this->redirect('list');
        } catch (ModelException $e) {
            $connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage('Chyba při zakládání řešitele.', self::FLASH_ERROR);
        }
    }

    /**
     * @internal
     * @param type $stepName
     * @param WizardComponent $wizard
     */
    public function initWizard($stepName, WizardComponent $wizard) {
        switch ($stepName) {
            case ContestantWizardFactory::STEP_DATA:
                $this->initStepData(
                        $wizard);
                break;
        }
    }

    /**
     * @internal
     * @param Form $form
     */
    public function handleContestantEditFormSuccess(Form $form) {
        $values = $form->getValues();
        $data = $values[self::CONT_CONTESTANT];
        $model = $this->getModel();

        try {
            $this->serviceContestant->updateModel($model, $data);
            $this->serviceContestant->save($model);
            $this->flashMessage(sprintf('Řešitel %s upraven.', $model->getPerson()->getFullname()), self::FLASH_SUCCESS);
            $this->redirect('list');
        } catch (ModelException $e) {

            $this->flashMessage('Chyba při ukládání do databáze.', self::FLASH_ERROR);
            Debugger::log($e);
        }
    }

    private function initStepData(WizardComponent $wizard) {
        $data = $wizard->getData(ContestantWizardFactory::STEP_PERSON);
        $person = $this->getPersonFromPersonStep($data);
        $form = $wizard->getComponent(ContestantWizardFactory::STEP_DATA);

        $defaults = array();

        $defaults[ContestantWizardFactory::CONT_PERSON] = $person->toArray();

        $lastContestant = $person->getLastContestant($this->getSelectedContest());
        if ($lastContestant) {
            $defaults[ContestantWizardFactory::CONT_CONTESTANT] = $lastContestant->toArray();
        }

        $addresses = array();
        foreach ($person->getMPostContacts() as $mPostContact) {
            $addresses[] = $mPostContact->toArray();
        }
        $defaults[ContestantWizardFactory::CONT_ADDRESSES] = $addresses;

        $info = $person->getInfo();
        if ($info) {
            $defaults[ContestantWizardFactory::CONT_PERSON_INFO] = $info->toArray();
        }

        // we know the person only right before initialization, so in this place the email rules are also set up
        $login = $person->getLogin();
        if ($login) {
            $form[ContestantWizardFactory::CONT_PERSON_INFO][PersonFactory::EL_CREATE_LOGIN]->setDisabled();
            $defaults[ContestantWizardFactory::CONT_PERSON_INFO]['email'] = $login->email;
            $emailRule = $this->uniqueEmailFactory->create(UniqueEmail::CHECK_LOGIN, null, $login);
        } else {
            // we are more restrictive, check both persons and logins because depending on the checkbox value, we store email either with login or with person
            $emailRule = $this->uniqueEmailFactory->create(UniqueEmail::CHECK_PERSON | UniqueEmail::CHECK_LOGIN, $person, null);
        }
        //$form[ContestantWizardFactory::CONT_PERSON_INFO]['email']->addCondition(Form::FILLED)->addRule($emailRule, 'Daný e-mail již někdo používá.');
        $form[ContestantWizardFactory::CONT_PERSON_INFO]['email']->addRule($emailRule, 'Daný e-mail již někdo používá.');

        $form->
                setDefaults($defaults);
    }

    /**
     * 
     * @param mixed $data
     * @return ModelPerson
     */
    private function getPersonFromPersonStep($data) {
        if ($data[ContestantWizardFactory::EL_PERSON_ID]) {
            $person = $this->servicePerson->findByPrimary($data[ContestantWizardFactory::EL_PERSON_ID]);
        } else {
            $dataPerson = $data[ContestantWizardFactory::CONT_PERSON];
            $dataPerson = FormUtils::emptyStrToNull($dataPerson);

            $person = $this->servicePerson->createNew($dataPerson);
        }
        return $person;
    }

    protected function createModel($id) {
        return $this->serviceContestant->findByPrimary($id);
    }

}


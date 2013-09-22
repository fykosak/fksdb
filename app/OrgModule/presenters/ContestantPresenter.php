<?php

namespace OrgModule;

use FKSDB\Components\Factories\ContestantWizardFactory;
use FKSDB\Components\Forms\Factories\ContestantFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Grids\ContestantsGrid;
use FKSDB\Components\WizardComponent;
use FormUtils;
use ModelContestant;
use ModelException;
use ModelPerson;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use ServiceContestant;
use ServiceLogin;
use ServiceMPostContact;
use ServicePerson;
use ServicePersonInfo;

class ContestantPresenter extends BasePresenter {

    const CONT_PERSON = 'person';
    const CONT_CONTESTANT = 'contestant';

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
     * @var ModelContestant
     */
    private $contestant;

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

    public function actionDefault() {
        if (!$this->getContestAuthorizator()->isAllowed('contestant', 'list', $this->getSelectedContest())) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }
    }

    public function actionCreate() {
        if (!$this->getContestAuthorizator()->isAllowed('contestant', 'create', $this->getSelectedContest())) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }
    }

    public function actionEdit($id) {
        $this->contestant = $this->serviceContestant->findByPrimary($id);

        if (!$this->contestant) {
            throw new BadRequestException('Neexistující řešitel.', 404);
        }
        if (!$this->getContestAuthorizator()->isAllowed($this->contestant, 'edit', $this->getSelectedContest())) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }
    }

    public function renderEdit($id) {
        if ($this->contestant->contest_id != $this->getSelectedContest()->contest_id) {
            $this->flashMessage('Editace řešitele mimo zvolený seminář.');
        }

        if ($this->contestant->year != $this->getSelectedYear()) {
            $this->flashMessage('Editace řešitele mimo zvolený ročník semináře.');
        }

        $form = $this->getComponent('contestantEditForm');

        $form[self::CONT_PERSON]->setValues($this->contestant->getPerson()->toArray());
        $form[self::CONT_CONTESTANT]->setDefaults($this->contestant->toArray());
    }

    protected function createComponentContestantWizard() {
        $wizard = $this->contestantWizardFactory->create();

        $wizard->onProcess[] = array($this, 'processWizard');
        $wizard->onStepInit[] = array($this, 'initWizard');

        return $wizard;
    }

    protected function createComponentGridContestants($name) {
        $grid = new ContestantsGrid($this->serviceContestant);

        return $grid;
    }

    protected function createComponentContestantEditForm($name) {
        $form = new Form();

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


        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }

            /*
             * Person
             */
            $data = $wizard->getData(ContestantWizardFactory::STEP_PERSON);
            $person = $this->getPersonFromPersonStep($data);
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
                    $login = $this->serviceLogin->createLoginWithInvitation($email);
                } else {
                    $dataInfo['email'] = $email; // we'll store it as personal info
                }
            }

            /*
             * Personal info
             */
            $personInfo = $person->getInfo();
            if (!$personInfo) {
                $personInfo = $this->servicePersonInfo->createNew($dataInfo);
                $personInfo->person_id = $person->person_id;
            } else {
                $this->servicePersonInfo->updateModel($personInfo, $dataInfo);
            }
            $this->servicePersonInfo->save($personInfo);

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new ModelException();
            }

            $this->flashMessage($person->gender == 'F' ? 'Řešitelka úspěšně založena.' : 'Řešitel úspěšně založen.');
            $this->redirect('Contestant:default');
        } catch (ModelException $e) {
            $connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage('Chyba při zakládání řešitele.', 'error');
            $this->redirect('Contestant:default');
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
                $this->initStepData($wizard);
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

        try {
            $this->serviceContestant->updateModel($this->contestant, $data);
            $this->serviceContestant->save($this->contestant);
            $this->flashMessage(sprintf('Řešitel %s upraven.', $this->contestant->getPerson()->getFullname()));
            $this->redirect('default');
        } catch (ModelException $e) {
            $this->flashMessage('Chyba při ukládání do databáze.');
            Debugger::log($e);
        }
    }

    private function initStepData(WizardComponent $wizard) {
        $data = $wizard->getData(ContestantWizardFactory::STEP_PERSON);
        $person = $this->getPersonFromPersonStep($data);

        $defaults = array();
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


        $form = $wizard->getComponent(ContestantWizardFactory::STEP_DATA);
        $form->setDefaults($defaults);
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

}

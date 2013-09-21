<?php

namespace OrgModule;

use FKSDB\Components\Factories\ContestantWizardFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Grids\ContestantsGrid;
use FKSDB\Components\WizardComponent;
use FormUtils;
use ModelException;
use ModelPerson;
use Nette\Diagnostics\Debugger;
use ServiceContestant;
use ServiceLogin;
use ServiceMPostContact;
use ServicePerson;
use ServicePersonInfo;

class ContestantPresenter extends BasePresenter {

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
     *
     * @var ContestantWizardFactory
     */
    private $contestantWizardFactory;

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

    public function actionCreate() {
        //TODO check ACL
    }

    protected function createComponentContestantWizard($name) {
        $wizard = $this->contestantWizardFactory->create();

        $wizard->onProcess[] = array($this, 'processWizard');
        $wizard->onStepInit[] = array($this, 'initWizard');

        return $wizard;
    }

    protected function createComponentGridContestants($name) {
        $grid = new ContestantsGrid($this->serviceContestant);

        return $grid;
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

            //TODO consider already existing person with addresses and login...
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

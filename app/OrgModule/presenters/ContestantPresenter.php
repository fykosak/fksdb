<?php

namespace OrgModule;

use AbstractModelSingle;
use Authentication\AccountManager;
use FKSDB\Components\Factories\ExtendedPersonWizardFactory;
use FKSDB\Components\Forms\Factories\ContestantFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Grids\ContestantsGrid;
use FKSDB\Components\WizardComponent;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Mail\MailTemplateFactory;
use ModelException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use OrgModule\EntityPresenter;
use Persons\ContestantHandler;
use Persons\PersonHandlerException;
use ServiceContestant;
use ServiceLogin;
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
     * @var ContestantFactory
     */
    private $contestantFactory;

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var ExtendedPersonWizardFactory
     */
    private $contestantWizardFactory;

    /**
     *
     * @var ContestantHandler
     */
    private $contestantHandler;

    public function injectServiceContestant(ServiceContestant $serviceContestant) {
        $this->serviceContestant = $serviceContestant;
    }

    public function injectContestantWizardFactory(ExtendedPersonWizardFactory $contestantWizardFactory) {
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

    public function injectContestantFactory(ContestantFactory $contestantFactory) {
        $this->contestantFactory = $contestantFactory;
    }

    public function injectPersonFactory(PersonFactory $personFactory) {
        $this->personFactory = $personFactory;
    }

    public function injectContestantHandler(ContestantHandler $contestantHandler) {
        $this->contestantHandler = $contestantHandler;
    }

    public function titleEdit($id) {
        $this->setTitle(sprintf(_('Úprava řešitele %s'), $this->getModel()->getPerson()->getFullname()));
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

    public function titleCreate() {
        $this->setTitle(_('Založit řešitele'));
    }

    public function titleList() {
        $this->setTitle(_('Řešitelé'));
    }

    protected function setDefaults(AbstractModelSingle $model, Form $form) {
        $form[self::CONT_PERSON]->setValues($this->getModel()->getPerson()->toArray());
        $form[self::CONT_CONTESTANT]->setDefaults($this->getModel()->toArray());
    }

    protected function createComponentCreateComponent($name) {
        $wizard = $this->contestantWizardFactory->createContestant();

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
        try {
            $this->contestantHandler->store($wizard, $this);
            $person = $this->contestantHandler->getPerson();
            $this->flashMessage(sprintf('Řešitel %s založen.', $person->getFullname()), self::FLASH_SUCCESS);
            $this->redirect('list');
        } catch (PersonHandlerException $e) {
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
            case ExtendedPersonWizardFactory::STEP_DATA:
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
        $person = $this->contestantHandler->loadPerson($wizard);
        $form = $wizard->getComponent(ExtendedPersonWizardFactory::STEP_DATA);

        $defaults = array(
            ExtendedPersonWizardFactory::CONT_PERSON => $person,
        );

        $lastContestant = $person->getLastContestant($this->getSelectedContest());
        if ($lastContestant) {
            $defaults[ExtendedPersonWizardFactory::CONT_CONTESTANT] = $lastContestant;
        }

        $addresses = array();
        foreach ($person->getMPostContacts() as $mPostContact) {
            $addresses[] = $mPostContact->toArray();
        }
        $defaults[ExtendedPersonWizardFactory::CONT_ADDRESSES] = $addresses;

        $info = $person->getInfo();
        if ($info) {
            $defaults[ExtendedPersonWizardFactory::CONT_PERSON_INFO] = $info;
        }

        $this->contestantWizardFactory->modifyLoginContainer($form, $person);

        $form->setDefaults($defaults);
    }

    protected function createModel($id) {
        return $this->serviceContestant->findByPrimary($id);
    }

}


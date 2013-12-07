<?php

namespace OrgModule;

use AbstractModelSingle;
use FKSDB\Components\Factories\ExtendedPersonWizardFactory;
use FKSDB\Components\Forms\Factories\OrgFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Grids\OrgsGrid;
use FKSDB\Components\WizardComponent;
use FormUtils;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use ModelException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use OrgModule\EntityPresenter;
use Persons\OrgHandler;
use Persons\PersonHandlerException;
use ServiceLogin;
use ServiceOrg;
use ServicePerson;
use ServicePersonInfo;

class OrgPresenter extends EntityPresenter {

    const CONT_PERSON = 'person';
    const CONT_ORG = 'contestant';

    protected $modelResourceId = 'org';

    /**
     * @var ServiceOrg
     */
    private $serviceOrg;

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
     * @var OrgFactory
     */
    private $orgFactory;

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var ExtendedPersonWizardFactory
     */
    private $orgWizardFactory;

    /**
     *
     * @var OrgHandler
     */
    private $orgHandler;

    public function injectServiceOrg(ServiceOrg $serviceOrg) {
        $this->serviceOrg = $serviceOrg;
    }

    public function injectOrgWizardFactory(ExtendedPersonWizardFactory $contestantWizardFactory) {
        $this->orgWizardFactory = $contestantWizardFactory;
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

    public function injectOrgFactory(OrgFactory $orgFactory) {
        $this->orgFactory = $orgFactory;
    }

    public function injectPersonFactory(PersonFactory $personFactory) {
        $this->personFactory = $personFactory;
    }

    public function injectOrgHandler(OrgHandler $orgHandler) {
        $this->orgHandler = $orgHandler;
    }

    public function titleEdit($id) {
        $this->setTitle(sprintf(_('Úprava organizátora %s'), $this->getModel()->getPerson()->getFullname()));
    }

    public function renderEdit($id) {
        parent::renderEdit($id);

        $org = $this->getModel();

        if ($org->contest_id != $this->getSelectedContest()->contest_id) {
            $this->flashMessage(_('Editace organizátora mimo zvolený seminář.'), self::FLASH_WARNING);
        }
    }

    public function titleCreate() {
        $this->setTitle(_('Založit organizátora'));
    }

    public function titleList() {
        $this->setTitle(_('Organizátoři'));
    }

    protected function setDefaults(AbstractModelSingle $model, Form $form) {
        $form[self::CONT_PERSON]->setValues($model->getPerson());
        $form[self::CONT_ORG]->setDefaults($model);
    }

    protected function createComponentCreateComponent($name) {
        $wizard = $this->orgWizardFactory->createOrg($this->getSelectedContest());

        $wizard->onProcess[] = array($this, 'processWizard');
        $wizard->onStepInit[] = array($this, 'initWizard');

        return $wizard;
    }

    protected function createComponentGrid($name) {
        $grid = new OrgsGrid($this->serviceOrg);

        return $grid;
    }

    protected function createComponentEditComponent($name) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());

        $personContainer = $this->personFactory->createPerson(PersonFactory::DISABLED);
        $form->addComponent($personContainer, self::CONT_PERSON);

        $orgContainer = $this->orgFactory->createOrg(0, null, $this->getSelectedContest());
        $form->addComponent($orgContainer, self::CONT_ORG);

        $form->addSubmit('send', _('Uložit'));

        $form->onSuccess[] = array($this, 'handleOrgEditFormSuccess');

        return $form;
    }

    /**
     * @internal
     * @param WizardComponent $wizard
     * @throws ModelException
     */
    public function processWizard(WizardComponent $wizard) {
        try {
            $this->orgHandler->store($wizard, $this);
            $person = $this->orgHandler->getPerson();
            $this->flashMessage(sprintf('Organizátor %s založen.', $person->getFullname()), self::FLASH_SUCCESS);
            $this->redirect('list');
        } catch (PersonHandlerException $e) {
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage(_('Chyba při zakládání organizátora.'), self::FLASH_ERROR);
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
                $this->initStepData($wizard);
                break;
        }
    }

    /**
     * @internal
     * @param Form $form
     */
    public function handleOrgEditFormSuccess(Form $form) {
        $values = $form->getValues();
        $data = $values[self::CONT_ORG];
        $data = FormUtils::emptyStrToNull($data);
        $model = $this->getModel();

        try {
            $this->serviceOrg->updateModel($model, $data);
            $this->serviceOrg->save($model);
            $this->flashMessage(sprintf('Organizátor %s upraven.', $model->getPerson()->getFullname()), self::FLASH_SUCCESS);
            $this->redirect('list');
        } catch (ModelException $e) {

            $this->flashMessage(_('Chyba při ukládání do databáze.'), self::FLASH_ERROR);
            Debugger::log($e);
        }
    }

    private function initStepData(WizardComponent $wizard) {
        $person = $this->orgHandler->loadPerson($wizard);
        $form = $wizard->getComponent(ExtendedPersonWizardFactory::STEP_DATA);

        $defaults = array(
            ExtendedPersonWizardFactory::CONT_PERSON => $person,
        );

        $org = $person->getOrgs($this->getSelectedContest()->contest_id)->fetch();
        if ($org) {
            $defaults[ExtendedPersonWizardFactory::CONT_ORG] = $org;
        } else {
            $defaults[ExtendedPersonWizardFactory::CONT_ORG]['since'] = $this->getSelectedYear();
        }

        $info = $person->getInfo();
        if ($info) {
            $defaults[ExtendedPersonWizardFactory::CONT_PERSON_INFO] = $info;
        }

        $personContainer = $form[ExtendedPersonWizardFactory::CONT_PERSON];
        $this->personFactory->modifyLoginContainer($personContainer, $person);

        $form->setDefaults($defaults);
    }

    protected function createModel($id) {
        return $this->serviceOrg->findByPrimary($id);
    }

}


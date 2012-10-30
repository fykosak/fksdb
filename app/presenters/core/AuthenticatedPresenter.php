<?php

use Nette\Http\UserStorage;
use Nette\Application\UI\Form;

/**
 */
abstract class AuthenticatedPresenter extends BasePresenter {

    /**
     *
     * @var int
     * @persistent
     */
    public $orgId;

    /**
     *
     * @var int
     * @persistent
     */
    public $year;
    protected $activeOrgs = array();

    protected function startup() {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->loginRedirect();
        }
        $this->initOrgs();
        //TODO zkontrolova, že je >= 1 org
    }

    protected function loginRedirect() {
        if ($this->user->logoutReason === UserStorage::INACTIVITY) {
            $this->flashMessage('Byl(a) jste příliš dlouho neaktivní a pro jistotu Vás systém odhlásil.');
        } else {
            $this->flashMessage('Musíte se přihlásit k přístupu na požadovanou stránku.');
        }
        $backlink = $this->application->storeRequest();
        $this->redirect('Authentication:login', array('backlink' => $backlink));
    }

    //
    // ------ org choosing -----
    //
    protected function initOrgs() {
        $yc = $this->getService('yearCalculator');
        $this->activeOrgs = $this->getUser()->getIdentity()->getActiveOrgs($yc);

        $ordIds = array_keys($this->activeOrgs);
        if ($this->orgId === null) {
            $this->orgId = $ordIds[0]; //first org
        }
        if (!isset($this->activeOrgs[$this->orgId])) {
            $this->handleChangeOrg($this->orgId);
        }
        if ($this->year === null) {
            $this->year = $yc->getCurrentYear($this->activeOrgs[$this->orgId]->contest_id);
        }
    }

    public function getActiveOrgs() {
        return $this->activeOrgs;
    }

    public function handleChangeOrg($orgId) {
        if (!isset($this->activeOrgs[$orgId])) {
            $ordIds = array_keys($this->activeOrgs);
            $orgId = $ordIds[0]; //first org
        }
        $this->orgId = $orgId;
        $yc = $this->getService('yearCalculator');
        $this->year = $yc->getCurrentYear($this->activeOrgs[$this->orgId]->contest_id);
        $this->redirect('this');
    }

    //
    // ----- year choosing ----
    //
    protected function createComponentFormSelectYear($name) {
        $form = new Form($this, $name);
        $yc = $this->getService('yearCalculator');
        $currentYear = $yc->getCurrentYear($this->activeOrgs[$this->orgId]->contest_id);

        $form->addSelect('year')
                ->setItems(range(1, $currentYear + 1), false)
                ->setDefaultValue($this->year);

        $form->addSubmit('change', 'Změnit');
        $form->onSuccess[] = array($this, 'handleChangeYear');
    }

    public function handleChangeYear($form) {
        $values = $form->getValues();
        $this->year = $values['year'];
        $this->redirect('this');
    }

//    public function getChosenYear() {
//        $sess = $this->getSession('contest');
//        $orgs = $this->getActiveOrgs();
//        $org = $orgs[$this->getChosenOrgId()];
//
//        if (!isset($sess->chosenYears) || !isset($sess->chosenYears[$org->contest_id])) {
//            $yc = $this->getService('yearCalculator');
//            $this->setChosenYear($yc->getCurrentYear($org->contest_id)); // active year by default
//        }
//        return $sess->chosenYears[$org->contest_id];
//    }
//
//    public function setChosenYear($year) {
//        $sess = $this->getSession('contest');
//        $orgs = $this->getActiveOrgs();
//        $org = $orgs[$this->getChosenOrgId()];
//        $yc = $this->getService('yearCalculator');
//
//        $year = $year;
//
//        if (!isset($sess->chosenYears)) {
//            $sess->chosenYears = array();
//        }
//        $sess->chosenYears[$org->contest_id] = $year;
//    }

    private $selectedContest;

    public function getSelectedContest() {
        if ($this->selectedContest === null) {
            $service = $this->context->getService('ServiceContest');
            $this->selectedContest = $service->findByPrimary(1); //TODO
        }
        return $this->selectedContest;
    }

    public function getSelectedYear() {
        return $this->year;
    }

}

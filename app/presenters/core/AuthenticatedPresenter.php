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
    public $contestId;

    /**
     *
     * @var int
     * @persistent
     */
    public $year;
    protected $availableContests = array();

    protected function startup() {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->loginRedirect();
        }
        $this->initContests();
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
    protected function initContests() {
        $yc = $this->getService('yearCalculator');
        $activeOrgs = $this->getUser()->getIdentity()->getActiveOrgs($yc);

        if (count($activeOrgs) == 0) {
            $this->flashMessage('Ještě (už) nemáš organizátorský přístup.');
            $this->getUser()->logout();
        }

        $contests = array();
        foreach ($activeOrgs as $org) {
            $this->availableContests[] = $org->contest;
            $contests[$org->contest_id] = true; // to get unique contests
        }
        $contestIds = array_keys($contests);
        
        $session = $this->getSession()->getSection('presets');

        $defaultContest = isset($session->defaultContest) ? $session->defaultContest : $contestIds[0]; // by default choose the first
        $defaultYear = isset($session->defaultYear) ? $session->defaultYear : $yc->getCurrentYear($this->contestId);

        if ($this->contestId === null) {
            $this->contestId = $defaultContest;
        }

        if (!isset($contests[$this->contestId])) {
            $this->handleChangeContest($defaultContest);
        }
        if ($this->year === null) {
            $this->year = $defaultYear;
        }
        
        // remember
        $session->defaultContest = $this->contestId;
        $session->defaultYear = $this->year;
    }

    public function getAvailableContests() {
        return $this->availableContests;
    }

    public function handleChangeContest($contestId) {
        $this->contestId = $contestId;
        $yc = $this->getService('yearCalculator');
        $this->year = $yc->getCurrentYear($this->contestId);
        $this->redirect('this');
    }

    //
    // ----- year choosing ----
    //
    protected function createComponentFormSelectYear($name) {
        $form = new Form($this, $name);
        $yc = $this->getService('yearCalculator');
        $currentYear = $yc->getCurrentYear($this->contestId);

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

    private $selectedContest;

    public function getSelectedContest() {
        if ($this->selectedContest === null) {
            $service = $this->context->getService('ServiceContest');
            $this->selectedContest = $service->findByPrimary($this->contestId);
        }
        return $this->selectedContest;
    }

    public function getSelectedYear() {
        return $this->year;
    }

}

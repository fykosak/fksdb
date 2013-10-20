<?php

namespace OrgModule;

use AuthenticatedPresenter;
use IContestPresenter;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

/**
 * Presenter keeps chosen contest and year in session.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends AuthenticatedPresenter implements IContestPresenter {

    /**
     * @var int
     * @persistent
     */
    public $contestId;

    /**
     * @var int
     * @persistent
     */
    public $year;

    /**
     * @var array of ModelContest
     */
    protected $availableContests = array();

    protected function startup() {
        parent::startup();
        $this->initContests();
    }

    protected function initContests() {
        $activeOrgs = $this->getUser()->getIdentity()->getActiveOrgs($this->yearCalculator);

        if (count($activeOrgs) == 0) {
            throw new BadRequestException('Není organizátorský účet.', 403);
        }

        $this->availableContests = array();
        foreach ($activeOrgs as $contestId => $orgId) {
            $this->availableContests[] = $this->serviceContest->findByPrimary($contestId);
        }

        $contestIds = array_keys($activeOrgs);

        $session = $this->getSession()->getSection('presets');

        $defaultContest = isset($session->defaultContest) ? $session->defaultContest : $contestIds[0]; // by default choose the first
        $defaultYear = isset($session->defaultYear) ? $session->defaultYear :
                (($this->getSelectedContest() !== null) ? $this->yearCalculator->getCurrentYear($this->getSelectedContest()) : null);

        if ($this->contestId === null) {
            $this->contestId = $defaultContest;
        }

        if (!in_array($this->contestId, $contestIds)) {
            $this->handleChangeContest($defaultContest); //change from the URL
        }
        if ($this->year === null) {
            $this->year = $defaultYear;
        }

        // remember
        $session->defaultContest = $this->contestId;
        $session->defaultYear = $this->year;
    }

    /**
     * @todo Move to ModelLogin class.
     * @deprecated
     * @return type
     */
    public function getAvailableContests() {
        return $this->availableContests;
    }

    public function handleChangeContest($contestId) {
        $this->contestId = $contestId;
        $this->selectedContest = null;
        $this->year = $this->yearCalculator->getCurrentYear($this->getSelectedContest());
        $this->redirect('this');
    }

    //
    // ----- year choosing ----
    //
     protected function createComponentFormSelectYear($name) {
        $form = new Form($this, $name);
        $currentYear = $this->yearCalculator->getCurrentYear($this->getSelectedContest());

        $form->addSelect('year', 'Ročník')
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

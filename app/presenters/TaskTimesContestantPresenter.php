<?php

use Nette\Application\UI\Form;

/**
 * Presenter "template" for presenters manipulating tasks x contestants tables.
 * 
 */
abstract class TaskTimesContestantPresenter extends AuthenticatedPresenter {

    /**
     * @var int
     * @persistent
     */
    public $series;

    /**
     * @param int $series when not null return only contestants with submits in the series
     * @return Nette\Database\Table\Selection
     */
    protected function getContestants($series = null) {
        $serviceContestant = $this->context->getService('ServiceContestant');
        return $serviceContestant->getTable()->where(array(
                    'contest_id' => $this->getSelectedContest()->contest_id,
                    'year' => $this->getSelectedYear(),
                ))->order('person.family_name, person.other_name');
        //TODO series
    }

    protected function getTasks() {
        $serviceTask = $this->context->getService('ServiceTask');
        return $serviceTask->getTable()->where(array(
                    'contest_id' => $this->getSelectedContest()->contest_id,
                    'year' => $this->getSelectedYear(),
                    'series' => $this->getSeries(),
                ))->order('tasknr');
    }

    /**
     * 
     * @return int
     */
    protected function getSeries() {
        return $this->series;
    }

    protected function getSubmitsTable() {
        $serviceSubmit = $this->context->getService('ServiceSubmit');

        $submits = $serviceSubmit->getTable()
                ->where('ct_id', $this->getContestants())
                ->where('task_id', $this->getTasks());

        // store submits in 2D hash for better access
        $submitsTable = array();
        foreach ($submits as $submit) {
            if (!isset($submitsTable[$submit->ct_id])) {
                $submitsTable[$submit->ct_id] = array();
            }
            $submitsTable[$submit->ct_id][$submit->task_id] = ModelSubmit::createFromTableRow($submit);
        }
        return $submitsTable;
    }

    //
    // ----- series choosing ----
    //
    protected function createComponentFormSelectSeries($name) {
        $form = new Form($this, $name);
        $sc = $this->getService('seriesCalculator');
        $lastSeries = $sc->getLastSeries($this->contestId, $this->year);

        $form->addSelect('series', 'Série')
                ->setItems(range(1, $lastSeries), false)
                ->setDefaultValue($this->series);

        $form->addSubmit('change', 'Změnit');
        $form->onSuccess[] = array($this, 'handleChangeSeries');
    }

    public function handleChangeSeries($form) {
        $values = $form->getValues();
        $this->series = $values['series'];
        $this->redirect('this');
    }

    protected function initSeries() {
        $sc = $this->getService('seriesCalculator');

        $session = $this->getSession()->getSection('presets');

        $defaultSeries = isset($session->defaultSeries) ? $session->defaultSeries : $sc->getCurrentSeries($this->contestId);
        $lastSeries = $sc->getLastSeries($this->contestId, $this->year);
        $defaultSeries = min($defaultSeries, $lastSeries);

        if ($this->series === null || $this->series > $lastSeries) {
            $this->series = $defaultSeries;
        }


        // remember
        $session->defaultSeries = $this->series;
    }

//    public function handleChangeContest($contestId) {
//        parent::handleChangeContest($contestId);
//        $this->series = null;
//    }
//
//    public function handleChangeYear($form) {
//        parent::handleChangeYear($form);
//        $this->series = null;
//    }

    protected function startup() {
        parent::startup();
        $this->initSeries();
    }

}

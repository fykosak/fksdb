<?php

namespace OrgModule;

use ModelSubmit;
use Nette\Application\UI\Form;
use Nette\Database\Table\Selection;
use SeriesCalculator;
use ServiceContestant;
use ServiceSubmit;
use ServiceTask;

/**
 * Presenter "template" for presenters manipulating tasks x contestants tables.
 * 
 */
abstract class TaskTimesContestantPresenter extends BasePresenter {

    /**
     * @var int
     * @persistent
     */
    public $series;

    /**
     * @var ServiceContestant
     */
    protected $serviceContestant;

    /**
     * @var ServiceTask
     */
    protected $serviceTask;

    /**
     * @var ServiceSubmit
     */
    protected $serviceSubmit;

    /**
     * @var SeriesCalculator
     */
    protected $seriesCalculator;

    public function injectServiceContestant(ServiceContestant $serviceContestant) {
        $this->serviceContestant = $serviceContestant;
    }

    public function injectServiceTask(ServiceTask $serviceTask) {
        $this->serviceTask = $serviceTask;
    }

    public function injectServiceSubmit(ServiceSubmit $serviceSubmit) {
        $this->serviceSubmit = $serviceSubmit;
    }

    public function injectSeriesCalculator(SeriesCalculator $seriesCalculator) {
        $this->seriesCalculator = $seriesCalculator;
    }

    /**
     * @param int $series when not null return only contestants with submits in the series
     * @return Selection
     */
    protected function getContestants($series = null) {
        return $this->serviceContestant->getTable()->where(array(
                    'contest_id' => $this->getSelectedContest()->contest_id,
                    'year' => $this->getSelectedYear(),
                ))->order('person.family_name, person.other_name');
        //TODO series
    }

    protected function getTasks() {
        return $this->serviceTask->getTable()->where(array(
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
        $submits = $this->serviceSubmit->getTable()
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
        $lastSeries = $this->seriesCalculator->getLastSeries($this->getSelectedContest(), $this->getSelectedYear());

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
        $session = $this->getSession()->getSection('presets');

        $defaultSeries = isset($session->defaultSeries) ? $session->defaultSeries : $this->seriesCalculator->getCurrentSeries($this->getSelectedContest());
        $lastSeries = $this->seriesCalculator->getLastSeries($this->getSelectedContest(), $this->getSelectedYear());
        $defaultSeries = min($defaultSeries, $lastSeries);

        if ($this->series === null || $this->series > $lastSeries) {
            $this->series = $defaultSeries;
        }

        // remember
        $session->defaultSeries = $this->series;
    }

    protected function startup() {
        parent::startup();
        $this->initSeries();
    }

}

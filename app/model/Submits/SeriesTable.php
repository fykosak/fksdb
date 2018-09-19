<?php

namespace Submits;

use ModelContest;
use ModelSubmit;
use Nette\Database\Table\Selection;
use ServiceContestant;
use ServiceSubmit;
use ServiceTask;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @todo Prominent example for necessity of caching.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SeriesTable {

    const FORM_SUBMIT = 'submit';
    const FORM_CONTESTANT = 'contestant';

    /**
     * @var ServiceContestant
     */
    private $serviceContestant;

    /**
     * @var ServiceTask
     */
    private $serviceTask;

    /**
     * @var ServiceSubmit
     */
    private $serviceSubmit;

    /**
     * @var ModelContest
     */
    private $contest;

    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $series;

    /**
     *
     * @var null|array of int IDs of allowed tasks or null for unrestricted
     */
    private $taskFilter;

    function __construct(ServiceContestant $serviceContestant, ServiceTask $serviceTask, ServiceSubmit $serviceSubmit) {
        $this->serviceContestant = $serviceContestant;
        $this->serviceTask = $serviceTask;
        $this->serviceSubmit = $serviceSubmit;
    }

    public function getContest() {
        return $this->contest;
    }

    public function setContest(ModelContest $contest) {
        $this->contest = $contest;
    }

    public function getYear() {
        return $this->year;
    }

    public function setYear($year) {
        $this->year = $year;
    }

    public function getSeries() {
        return $this->series;
    }

    public function setSeries($series) {
        $this->series = $series;
    }

    public function getTaskFilter() {
        return $this->taskFilter;
    }

    public function setTaskFilter($taskFilter) {
        $this->taskFilter = $taskFilter;
    }

    /**
     * @param int $series when not null return only contestants with submits in the series
     * @return Selection
     */
    public function getContestants($series = null) {
        return $this->serviceContestant->getTable()->where(array(
            'contest_id' => $this->getContest()->contest_id,
            'year' => $this->getYear(),
        ))->order('person.family_name, person.other_name, person.person_id');
        //TODO series
    }

    public function getTasks() {
        $tasks = $this->serviceTask->getTable()->where(array(
            'contest_id' => $this->getContest()->contest_id,
            'year' => $this->getYear(),
            'series' => $this->getSeries(),
        ));

        if ($this->getTaskFilter() !== null) {
            $tasks->where('task_id', $this->getTaskFilter());
        }
        return $tasks->order('tasknr');
    }

    public function getSubmitsTable($ctId = null, $task = null) {
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

        if ($ctId !== null) {
            return $submitsTable[$ctId];
        }
        return $submitsTable;
    }

    /**
     * @return array
     */
    public function formatAsFormValues() {
        $submitsTable = $this->getSubmitsTable();
        $contestants = $this->getContestants();
        $result = array();
        foreach ($contestants as $contestant) {
            $ctId = $contestant->ct_id;
            if (isset($submitsTable[$ctId])) {
                $result[$ctId] = array(self::FORM_SUBMIT => $submitsTable[$ctId]);
            } else {
                $result[$ctId] = array(self::FORM_SUBMIT => null);
            }
        }
        return array(
            self::FORM_CONTESTANT => $result
        );
    }

    /**
     * @return array
     */
    public function getFingerprint() {
        $fingerprint = '';
        foreach ($this->getSubmitsTable() as $submits) {
            foreach ($submits as $submit) {
                $fingerprint .= $submit->getFingerprint();
            }
        }
        return md5($fingerprint);
    }

}

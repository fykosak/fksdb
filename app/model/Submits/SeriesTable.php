<?php

namespace FKSDB\Submits;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceContestant;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\ORM\Services\ServiceTask;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Database\Table\Selection;

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

    /**
     * SeriesTable constructor.
     * @param ServiceContestant $serviceContestant
     * @param ServiceTask $serviceTask
     * @param ServiceSubmit $serviceSubmit
     */
    function __construct(ServiceContestant $serviceContestant, ServiceTask $serviceTask, ServiceSubmit $serviceSubmit) {
        $this->serviceContestant = $serviceContestant;
        $this->serviceTask = $serviceTask;
        $this->serviceSubmit = $serviceSubmit;
    }

    /**
     * @return ModelContest
     */
    public function getContest(): ModelContest {
        return $this->contest;
    }

    /**
     * @param ModelContest $contest
     */
    public function setContest(ModelContest $contest) {
        $this->contest = $contest;
    }

    /**
     * @return int
     */
    public function getYear(): int {
        return $this->year;
    }

    /**
     * @param $year
     */
    public function setYear(int $year) {
        $this->year = $year;
    }

    /**
     * @return int
     */
    public function getSeries(): int {
        return $this->series;
    }

    /**
     * @param $series
     */
    public function setSeries(int $series) {
        $this->series = $series;
    }

    /**
     * @return array|null
     */
    public function getTaskFilter() {
        return $this->taskFilter;
    }

    /**
     * @param array|null $taskFilter
     */
    public function setTaskFilter($taskFilter) {
        $this->taskFilter = $taskFilter;
    }

    /**
     * @return Selection
     */
    public function getContestants(): Selection {
        return $this->serviceContestant->getTable()->where([
            'contest_id' => $this->getContest()->contest_id,
            'year' => $this->getYear(),
        ])->order('person.family_name, person.other_name, person.person_id');
    }

    /**
     * @return Selection
     */
    public function getTasks(): Selection {
        $tasks = $this->serviceTask->getTable()->where([
            'contest_id' => $this->getContest()->contest_id,
            'year' => $this->getYear(),
            'series' => $this->getSeries(),
        ]);

        if ($this->getTaskFilter() !== null) {
            $tasks->where('task_id', $this->getTaskFilter());
        }
        return $tasks->order('tasknr');
    }

    /**
     * @return TypedTableSelection
     */
    public function getSubmits(): TypedTableSelection {
        return $this->serviceSubmit->getTable()
            ->where('ct_id', $this->getContestants())
            ->where('task_id', $this->getTasks());
    }

    /**
     * @return array
     */
    public function getSubmitsTable(): array {
        $submits = $this->getSubmits();

        // store submits in 2D hash for better access
        $submitsTable = [];
        foreach ($submits as $row) {
            $submit = ModelSubmit::createFromActiveRow($row);
            if (!isset($submitsTable[$submit->ct_id])) {
                $submitsTable[$submit->ct_id] = [];
            }
            $submitsTable[$submit->ct_id][$submit->task_id] = $submit;
        }
        return $submitsTable;
    }

    /**
     * @return array
     */
    public function formatAsFormValues(): array {
        $submitsTable = $this->getSubmitsTable();
        $contestants = $this->getContestants();
        $result = [];
        foreach ($contestants as $contestantRow) {
            $contestant = ModelContestant::createFromActiveRow($contestantRow);
            $ctId = $contestant->ct_id;
            if (isset($submitsTable[$ctId])) {
                $result[$ctId] = [self::FORM_SUBMIT => $submitsTable[$ctId]];
            } else {
                $result[$ctId] = [self::FORM_SUBMIT => null];
            }
        }
        return [
            self::FORM_CONTESTANT => $result
        ];
    }

    /**
     * @return string
     */
    public function getFingerprint(): string {
        $fingerprint = '';
        foreach ($this->getSubmitsTable() as $submits) {
            foreach ($submits as $submit) {
                /**
                 * @var ModelSubmit $submit
                 */
                $fingerprint .= $submit->getFingerprint();
            }
        }
        return md5($fingerprint);
    }
}

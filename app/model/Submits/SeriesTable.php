<?php

namespace FKSDB\Submits;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceContestant;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\ORM\Services\ServiceTask;
use FKSDB\ORM\Tables\TypedTableSelection;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @todo Prominent example for necessity of caching.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SeriesTable {

    public const FORM_SUBMIT = 'submit';
    public const FORM_CONTESTANT = 'contestant';

    private ServiceContestant $serviceContestant;

    private ServiceTask $serviceTask;

    private ServiceSubmit $serviceSubmit;

    private ModelContest $contest;

    private int $year;

    private int $series;

    /**
     *
     * @var null|array of int IDs of allowed tasks or null for unrestricted
     */
    private ?array $taskFilter;

    /**
     * SeriesTable constructor.
     * @param ServiceContestant $serviceContestant
     * @param ServiceTask $serviceTask
     * @param ServiceSubmit $serviceSubmit
     */
    public function __construct(ServiceContestant $serviceContestant, ServiceTask $serviceTask, ServiceSubmit $serviceSubmit) {
        $this->serviceContestant = $serviceContestant;
        $this->serviceTask = $serviceTask;
        $this->serviceSubmit = $serviceSubmit;
    }

    public function getContest(): ModelContest {
        return $this->contest;
    }

    public function setContest(ModelContest $contest): void {
        $this->contest = $contest;
    }

    public function getYear(): int {
        return $this->year;
    }

    public function setYear(int $year): void {
        $this->year = $year;
    }

    public function getSeries(): int {
        return $this->series;
    }

    public function setSeries(int $series): void {
        $this->series = $series;
    }

    public function getTaskFilter(): ?array {
        return $this->taskFilter;
    }

    public function setTaskFilter(?array $taskFilter): void {
        $this->taskFilter = $taskFilter;
    }

    public function getContestants(): TypedTableSelection {
        return $this->serviceContestant->getTable()->where([
            'contest_id' => $this->getContest()->contest_id,
            'year' => $this->getYear(),
        ])->order('person.family_name, person.other_name, person.person_id');
    }

    public function getTasks(): TypedTableSelection {
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

    public function getSubmits(): TypedTableSelection {
        return $this->serviceSubmit->getTable()
            ->where('ct_id', $this->getContestants())
            ->where('task_id', $this->getTasks());
    }

    public function getSubmitsTable(): array {
        $submits = $this->getSubmits();

        // store submits in 2D hash for better access
        $submitsTable = [];
        /** @var ModelSubmit $submit */
        foreach ($submits as $submit) {
            if (!isset($submitsTable[$submit->ct_id])) {
                $submitsTable[$submit->ct_id] = [];
            }
            $submitsTable[$submit->ct_id][$submit->task_id] = $submit;
        }
        return $submitsTable;
    }

    public function formatAsFormValues(): array {
        $submitsTable = $this->getSubmitsTable();
        $contestants = $this->getContestants();
        $result = [];
        /** @var ModelContestant $contestant */
        foreach ($contestants as $contestant) {
            $ctId = $contestant->ct_id;
            if (isset($submitsTable[$ctId])) {
                $result[$ctId] = [self::FORM_SUBMIT => $submitsTable[$ctId]];
            } else {
                $result[$ctId] = [self::FORM_SUBMIT => null];
            }
        }
        return [
            self::FORM_CONTESTANT => $result,
        ];
    }

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

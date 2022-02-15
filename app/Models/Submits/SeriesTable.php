<?php

declare(strict_types=1);

namespace FKSDB\Models\Submits;

use FKSDB\Models\ORM\Models\{
    ModelContestant,
    ModelContestYear,
    ModelSubmit,
};
use FKSDB\Models\ORM\Services\{
    ServiceContestant,
    ServiceSubmit,
    ServiceTask,
};
use Fykosak\NetteORM\TypedTableSelection;

class SeriesTable
{

    public const FORM_SUBMIT = 'submit';
    public const FORM_CONTESTANT = 'contestant';

    private ServiceContestant $serviceContestant;
    private ServiceTask $serviceTask;
    private ServiceSubmit $serviceSubmit;

    public ModelContestYear $contestYear;
    public int $series;

    /**
     *
     * @var null|array of int IDs of allowed tasks or null for unrestricted
     */
    public ?array $taskFilter = null;

    public function __construct(
        ServiceContestant $serviceContestant,
        ServiceTask $serviceTask,
        ServiceSubmit $serviceSubmit
    ) {
        $this->serviceContestant = $serviceContestant;
        $this->serviceTask = $serviceTask;
        $this->serviceSubmit = $serviceSubmit;
    }

    public function getContestants(): TypedTableSelection
    {
        return $this->serviceContestant->getTable()->where([
            'contest_id' => $this->contestYear->contest_id,
            'year' => $this->contestYear->year,
        ])->order('person.family_name, person.other_name, person.person_id');
    }

    public function getTasks(): TypedTableSelection
    {
        $tasks = $this->serviceTask->getTable()->where([
            'contest_id' => $this->contestYear->contest_id,
            'year' => $this->contestYear->year,
            'series' => $this->series,
        ]);

        if (!isset($this->taskFilter)) {
            $tasks->where('task_id', $this->taskFilter);
        }
        return $tasks->order('tasknr');
    }

    public function getSubmits(): TypedTableSelection
    {
        return $this->serviceSubmit->getTable()
            ->where('ct_id', $this->getContestants())
            ->where('task_id', $this->getTasks());
    }

    public function getSubmitsTable(): array
    {
        // store submits in 2D hash for better access
        $submitsTable = [];
        /** @var ModelSubmit $submit */
        foreach ($this->getSubmits() as $submit) {
            $submitsTable[$submit->ct_id] = $submitsTable[$submit->ct_id] ?? [];
            $submitsTable[$submit->ct_id][$submit->task_id] = $submit;
        }
        return $submitsTable;
    }

    public function formatAsFormValues(): array
    {
        $submitsTable = $this->getSubmitsTable();
        $contestants = $this->getContestants();
        $result = [];
        /** @var ModelContestant $contestant */
        foreach ($contestants as $contestant) {
            $result[$contestant->ct_id] = [self::FORM_SUBMIT => $submitsTable[$contestant->ct_id] ?? null];
        }
        return [
            self::FORM_CONTESTANT => $result,
        ];
    }

    public function getFingerprint(): string
    {
        $fingerprint = '';
        foreach ($this->getSubmitsTable() as $submits) {
            foreach ($submits as $submit) {
                /** @var ModelSubmit $submit */
                $fingerprint .= $submit->getFingerprint();
            }
        }
        return md5($fingerprint);
    }
}

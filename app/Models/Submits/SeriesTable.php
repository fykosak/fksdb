<?php

declare(strict_types=1);

namespace FKSDB\Models\Submits;

use FKSDB\Models\ORM\Models\{
    ContestantModel,
    ContestYearModel,
    SubmitModel,
};
use FKSDB\Models\ORM\Services\{
    ContestantService,
    SubmitService,
    TaskService,
};
use Fykosak\NetteORM\TypedSelection;

class SeriesTable
{

    public const FORM_SUBMIT = 'submit';
    public const FORM_CONTESTANT = 'contestant';

    private ContestantService $contestantService;
    private TaskService $taskService;
    private SubmitService $submitService;

    public ContestYearModel $contestYear;
    public int $series;

    /**
     *
     * @var null|array of int IDs of allowed tasks or null for unrestricted
     */
    public ?array $taskFilter = null;

    public function __construct(
        ContestantService $contestantService,
        TaskService $taskService,
        SubmitService $submitService
    ) {
        $this->contestantService = $contestantService;
        $this->taskService = $taskService;
        $this->submitService = $submitService;
    }

    public function getContestants(): TypedSelection
    {
        return $this->contestantService->getTable()->where([
            'contest_id' => $this->contestYear->contest_id,
            'year' => $this->contestYear->year,
        ])->order('person.family_name, person.other_name, person.person_id');
    }

    public function getTasks(): TypedSelection
    {
        $tasks = $this->taskService->getTable()->where([
            'contest_id' => $this->contestYear->contest_id,
            'year' => $this->contestYear->year,
            'series' => $this->series,
        ]);

        if (isset($this->taskFilter)) {
            $tasks->where('task_id', $this->taskFilter);
        }
        return $tasks->order('tasknr');
    }

    public function getSubmits(): TypedSelection
    {
        return $this->submitService->getTable()
            ->where('contestant_id', $this->getContestants())
            ->where('task_id', $this->getTasks());
    }

    public function getSubmitsTable(): array
    {
        // store submits in 2D hash for better access
        $submitsTable = [];
        /** @var SubmitModel $submit */
        foreach ($this->getSubmits() as $submit) {
            $submitsTable[$submit->contestant_id] = $submitsTable[$submit->contestant_id] ?? [];
            $submitsTable[$submit->contestant_id][$submit->task_id] = $submit;
        }
        return $submitsTable;
    }

    public function formatAsFormValues(): array
    {
        $submitsTable = $this->getSubmitsTable();
        $contestants = $this->getContestants();
        $result = [];
        /** @var ContestantModel $contestant */
        foreach ($contestants as $contestant) {
            $result[$contestant->contestant_id] = [
                self::FORM_SUBMIT => $submitsTable[$contestant->contestant_id] ?? null,
            ];
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
                /** @var SubmitModel $submit */
                $fingerprint .= $submit->getFingerprint();
            }
        }
        return md5($fingerprint);
    }
}

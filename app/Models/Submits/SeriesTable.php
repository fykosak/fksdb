<?php

declare(strict_types=1);

namespace FKSDB\Models\Submits;

use FKSDB\Models\ORM\Models\{ContestantModel, ContestYearModel, SubmitModel,};
use FKSDB\Models\ORM\Services\SubmitService;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\NetteORM\TypedSelection;

class SeriesTable
{

    public const FORM_SUBMIT = 'submit';
    public const FORM_CONTESTANT = 'contestant';

    private SubmitService $submitService;

    public ContestYearModel $contestYear;
    public int $series;

    /**
     *
     * @var null|callable
     */
    public $taskFilter = null;

    public function __construct(SubmitService $submitService)
    {
        $this->submitService = $submitService;
    }

    public function getContestants(): TypedGroupedSelection
    {
        return $this->contestYear->getContestants()->order('person.family_name, person.other_name, person.person_id');
    }

    public function getTasks(): TypedGroupedSelection
    {
        $tasks = $this->contestYear->getTasks($this->series);
        if (isset($this->taskFilter)) {
            ($this->taskFilter)($tasks);
        }
        return $tasks->order('tasknr');
    }

    public function getSubmits(): TypedSelection
    {
        return $this->submitService->getTable()
            ->where('contestant_id', $this->getContestants()->fetchPairs('contestant_id'))
            ->where('task_id', $this->getTasks()->fetchPairs('task_id'));
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

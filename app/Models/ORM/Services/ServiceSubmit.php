<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\ModelTask;
use Fykosak\NetteORM\Service;

/**
 * @method SubmitModel findByPrimary($key)
 * @method SubmitModel createNewModel(array $data)
 * @method SubmitModel storeModel(array $data, ?SubmitModel $model = null)
 */
class ServiceSubmit extends Service
{

    private array $submitCache = [];

    public function findByContestantId(int $ctId, int $taskId, bool $useCache = true): ?SubmitModel
    {
        $key = $ctId . ':' . $taskId;
        if (!isset($this->submitCache[$key]) || !$useCache) {
            $result = $this->getTable()->where([
                'ct_id' => $ctId,
                'task_id' => $taskId,
            ])->fetch();
            $this->submitCache[$key] = $result ?? null;
        }
        return $this->submitCache[$key];
    }

    public function findByContestant(ModelContestant $contestant, ModelTask $task, bool $useCache = true): ?SubmitModel
    {
        $key = $contestant->ct_id . ':' . $task->task_id;
        if (!isset($this->submitCache[$key]) || !$useCache) {
            $row = $contestant->related(DbNames::TAB_SUBMIT)->where('task_id', $task->task_id)->fetch();
            $this->submitCache[$key] = $row ? SubmitModel::createFromActiveRow($row, $this->mapper) : null;
        }
        return $this->submitCache[$key];
    }

    public static function serializeSubmit(?SubmitModel $submit, ModelTask $task, ?int $studyYear): array
    {
        return [
            'submitId' => $submit ? $submit->submit_id : null,
            'name' => $task->getFQName(),
            'deadline' => sprintf(_('Deadline %s'), $task->submit_deadline),
            'taskId' => $task->task_id,
            'isQuiz' => count($task->related(DbNames::TAB_QUIZ)) > 0,
            'disabled' => !in_array($studyYear, array_keys($task->getStudyYears())),
        ];
    }
}

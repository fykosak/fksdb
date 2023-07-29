<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestCategoryModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use Fykosak\NetteORM\Service;

/**
 * @phpstan-extends Service<SubmitModel>
 */
final class SubmitService extends Service
{

    private array $submitCache = [];

    public function findByContestantId(ContestantModel $contestant, int $taskId, bool $useCache = true): ?SubmitModel
    {
        $key = $contestant->contestant_id . ':' . $taskId;
        if (!isset($this->submitCache[$key]) || !$useCache) {
            $this->submitCache[$key] = $contestant->getSubmits()->where('task_id', $taskId)->fetch();
        }
        return $this->submitCache[$key];
    }

    public function findByContestant(ContestantModel $contestant, TaskModel $task, bool $useCache = true): ?SubmitModel
    {
        $key = $contestant->contestant_id . ':' . $task->task_id;
        if (!isset($this->submitCache[$key]) || !$useCache) {
            $this->submitCache[$key] = $contestant->getSubmits()->where('task_id', $task->task_id)->fetch();
        }
        return $this->submitCache[$key];
    }

    public static function serializeSubmit(
        ?SubmitModel $submit,
        TaskModel $task,
        ?ContestCategoryModel $category
    ): array {
        return [
            'submitId' => $submit ? $submit->submit_id : null,
            'name' => $task->name->__serialize(),
            'deadline' => $task->submit_deadline ? sprintf(
                _('Deadline %s'),
                $task->submit_deadline->format(_('__date_time'))
            ) : null,
            'taskId' => $task->task_id,
            'isQuiz' => count($task->getQuestions()) > 0,
            'disabled' => !$task->isForCategory($category),
        ];
    }
}

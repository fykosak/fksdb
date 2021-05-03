<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\ModelSubmit;
use FKSDB\Models\ORM\Models\ModelTask;
use Fykosak\NetteORM\AbstractService;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelSubmit findByPrimary($key)
 * @method ModelSubmit createNewModel(array $data)
 */
class ServiceSubmit extends AbstractService {

    private array $submitCache = [];

    public function findByContestantId(int $ctId, int $taskId, bool $useCache = true): ?ModelSubmit {
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

    public function findByContestant(ModelContestant $contestant, ModelTask $task, bool $useCache = true): ?ModelSubmit {
        $key = $contestant->ct_id . ':' . $task->task_id;
        if (!isset($this->submitCache[$key]) || !$useCache) {
            $row = $contestant->related(DbNames::TAB_SUBMIT)->where('task_id', $task->task_id)->fetch();
            $this->submitCache[$key] = $row ? ModelSubmit::createFromActiveRow($row) : null;
        }
        return $this->submitCache[$key];
    }

    public static function serializeSubmit(?ModelSubmit $submit, ModelTask $task, ?int $studyYear): array {
        $test = count($task->related(DbNames::TAB_QUIZ));
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

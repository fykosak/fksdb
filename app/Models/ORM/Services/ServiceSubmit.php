<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContestant;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelSubmit;
use FKSDB\Models\ORM\Models\ModelTask;
use Fykosak\NetteORM\AbstractService;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelSubmit findByPrimary($key)
 * @method ModelSubmit createNewModel(array $data)
 * @method ModelSubmit refresh(AbstractModel $model)
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
            if ($result !== false) {
                $this->submitCache[$key] = $result;
            } else {
                $this->submitCache[$key] = null;
            }
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
        return [
            'submitId' => $submit ? $submit->submit_id : null,
            'name' => $task->getFQName(),
            'taskId' => $task->task_id,
            'deadline' => sprintf(_('Deadline %s'), $task->submit_deadline),
            'disabled' => !in_array($studyYear, array_keys($task->getStudyYears())),
        ];
    }
}

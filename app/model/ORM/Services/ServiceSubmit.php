<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelSubmit findByPrimary($key)
 * @method ModelSubmit createNewModel(array $data)
 * @method ModelSubmit refresh(AbstractModelSingle $model)
 */
class ServiceSubmit extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    private array $submitCache = [];

    /**
     * ServiceSubmit constructor.
     * @param Context $connection
     * @param IConventions $conventions
     */
    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_SUBMIT, ModelSubmit::class);
    }
    /**
     * Syntactic sugar.
     *
     * @param int $ctId
     * @param int $taskId
     * @param bool $useCache
     * @return ModelSubmit|null
     */
    public function findByContestant(int $ctId, int $taskId, bool $useCache = true): ?ModelSubmit {
        $key = $ctId . ':' . $taskId;
        if (!isset($this->submitCache[$key]) || is_null($this->submitCache[$key]) || !$useCache) {
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

    public function getSubmits(): TypedTableSelection {
        return $this->getTable()
            ->select(DbNames::TAB_SUBMIT . '.*')
            ->select(DbNames::TAB_TASK . '.*');
    }

    /**
     * @param ModelSubmit|null $submit
     * @param array $data
     * @return ModelSubmit
     */
    public function store($submit, array $data): ModelSubmit {
        if (is_null($submit)) {
            return $this->createNewModel($data);
        } else {
            $this->updateModel2($submit, $data);
            return $this->refresh($submit);
        }
    }

    /**
     * @param ModelSubmit|null $submit
     * @param ModelTask $task
     * @param int|null $studyYear
     * @return array
     */
    public static function serializeSubmit($submit, ModelTask $task, $studyYear): array {
        return [
            'submitId' => $submit ? $submit->submit_id : null,
            'name' => $task->getFQName(),
            'taskId' => $task->task_id,
            'deadline' => sprintf(_('Deadline %s'), $task->submit_deadline),
            'disabled' => !in_array($studyYear, array_keys($task->getStudyYears())),
        ];
    }
}

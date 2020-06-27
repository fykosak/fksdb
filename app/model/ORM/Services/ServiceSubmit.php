<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceSubmit extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    /**
     * @var array
     */
    private $submitCache = [];

    public function getModelClassName(): string {
        return ModelSubmit::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_SUBMIT;
    }

    /**
     * Syntactic sugar.
     *
     * @param int $ctId
     * @param int $taskId
     * @return ModelSubmit|null
     */
    public function findByContestant(int $ctId, int $taskId) {
        $key = $ctId . ':' . $taskId;

        if (!array_key_exists($key, $this->submitCache)) {
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
     * @param ModelTask $task
     * @param Presenter $presenter
     * @return array
     * @throws InvalidLinkException
     */
    public static function serializeSubmit($submit, ModelTask $task, Presenter $presenter): array {
        return [
            'submitId' => $submit ? $submit->submit_id : null,
            'name' => $task->getFQName(),
            'href' => $submit ? $presenter->link('download', ['id' => $submit->submit_id]) : null,
            'taskId' => $task->task_id,
            'deadline' => sprintf(_('Termín %s'), $task->submit_deadline),
        ];
    }
}

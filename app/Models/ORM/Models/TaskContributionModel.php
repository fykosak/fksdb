<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model;

/**
 * @property-read int contribution_id
 * @property-read int task_id
 * @property-read TaskModel task
 * @property-read int person_id
 * @property-read PersonModel person
 * @property-read TaskContributionType type
 */
class TaskContributionModel extends Model
{
    public function getContest(): ContestModel
    {
        return $this->task->contest;
    }

    /**
     * @param string $key
     * @return TaskContributionType|FakeStringEnum|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key)
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'type':
                $value = TaskContributionType::tryFrom($value);
                break;
        }
        return $value;
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;

/**
 * @property-read int $contribution_id
 * @property-read int $task_id
 * @property-read TaskModel $task
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read TaskContributionType $type
 */
final class TaskContributionModel extends Model
{
    /**
     * @return TaskContributionType|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'type':
                $value = TaskContributionType::from($value);
                break;
        }
        return $value;
    }
}

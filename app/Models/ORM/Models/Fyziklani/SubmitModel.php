<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\Fyziklani\Submit\AlreadyRevokedSubmitException;
use FKSDB\Models\Fyziklani\Submit\ClosedSubmittingException;
use Fykosak\NetteORM\Model;
use Nette\Security\Resource;

/**
 * @property-read SubmitState state
 * @property-read int fyziklani_team_id
 * @property-read int|null points
 * @property-read int|null skipped
 * @property-read int fyziklani_task_id
 * @property-read int fyziklani_submit_id
 * @property-read int task_id
 * @property-read TeamModel2 fyziklani_team
 * @property-read TaskModel fyziklani_task
 * @property-read \DateTimeInterface modified
 */
class SubmitModel extends Model implements Resource
{
    public const RESOURCE_ID = 'fyziklani.submit';

    public function isChecked(): bool
    {
        return $this->state->value === SubmitState::CHECKED;
    }

    public function __toArray(): array
    {
        return [
            'points' => $this->points,
            'teamId' => $this->fyziklani_team_id,
            'taskId' => $this->fyziklani_task_id,
            'created' => $this->modified->format('c'),
        ];
    }

    /**
     * @throws AlreadyRevokedSubmitException
     * @throws ClosedSubmittingException
     */
    public function canRevoke(bool $throws = true): bool
    {
        if (is_null($this->points)) {
            if ($throws) {
                throw new AlreadyRevokedSubmitException();
            }
            return false;
        } elseif (!$this->fyziklani_team->hasOpenSubmitting()) {
            if ($throws) {
                throw new ClosedSubmittingException($this->fyziklani_team);
            }
            return false;
        }
        return true;
    }

    /**
     * @param string $key
     * @return SubmitState|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key)
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'state':
                $value = SubmitState::tryFrom($value);
                break;
        }
        return $value;
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
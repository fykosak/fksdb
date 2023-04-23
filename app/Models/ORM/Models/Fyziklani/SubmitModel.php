<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Components\Game\Submits\AlreadyRevokedSubmitException;
use FKSDB\Components\Game\Submits\ClosedSubmittingException;
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
    public const RESOURCE_ID = 'game.submit';

    public function __toArray(): array
    {
        return [
            'points' => $this->points,
            'teamId' => $this->fyziklani_team_id,
            'taskId' => $this->fyziklani_task_id,
            'modified' => $this->modified->format('c'),
        ];
    }

    /**
     * @throws AlreadyRevokedSubmitException
     * @throws ClosedSubmittingException
     */
    public function canRevoke(): void
    {
        if (is_null($this->points)) {
            throw new AlreadyRevokedSubmitException();
        } elseif (!$this->fyziklani_team->hasOpenSubmitting()) {
            throw new ClosedSubmittingException($this->fyziklani_team);
        }
    }

    /**
     * @param string $key
     * @return SubmitState|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
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

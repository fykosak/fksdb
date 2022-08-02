<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Resource;
use Fykosak\NetteORM\Model;

/**
 * @property-read int submit_id
 * @property-read int ct_id
 * @property-read ActiveRow contestant_base TODO
 * @property-read int task_id
 * @property-read TaskModel task
 * @property-read \DateTimeInterface submitted_on
 * @property-read SubmitSource source
 * @property-read string note
 * @property-read float raw_points
 * @property-read float calc_points
 * @property-read bool corrected
 */
class SubmitModel extends Model implements Resource
{
    public function isEmpty(): bool
    {
        return !($this->submitted_on || $this->note);
    }

    public function getContestant(): ContestantModel
    {
        // TODO why?
        return ContestantModel::createFromActiveRow($this->ref(DbNames::TAB_CONTESTANT_BASE, 'ct_id'));
    }

    public function getResourceId(): string
    {
        return 'submit';
    }

    public function getFingerprint(): string
    {
        return md5(
            implode(':', [
                $this->submit_id,
                $this->submitted_on,
                $this->source,
                $this->note,
                $this->raw_points,
            ])
        );
    }

    public function canRevoke(): bool
    {
        if ($this->source->value != SubmitSource::UPLOAD) {
            return false;
        }
        $now = time();
        $start = $this->task->submit_start ? $this->task->submit_start->getTimestamp() : 0;
        $deadline = $this->task->submit_deadline ? $this->task->submit_deadline->getTimestamp() : ($now + 1);
        return ($now <= $deadline) && ($now >= $start);
    }

    public function isQuiz(): bool
    {
        return $this->source->value === SubmitSource::QUIZ;
    }

    /**
     * @return SubmitSource|FakeStringEnum|mixed|ActiveRow|null
     * @throws \ReflectionException
     */
    public function &__get(string $key)
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'source':
                $value = SubmitSource::tryFrom($value);
                break;
        }
        return $value;
    }
}

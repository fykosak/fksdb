<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Resource;
use Fykosak\NetteORM\Model;

/**
 * @property-read \DateTimeInterface submitted_on
 * @property-read int submit_id
 * @property-read string source
 * @property-read string note
 * @property-read int raw_points
 * @property-read int points
 * @property-read int ct_id
 * @property-read ActiveRow contestant_base
 * @property-read int task_id
 * @property-read ModelTask task
 * @property-read bool corrected
 */
class SubmitModel extends Model implements Resource
{

    public const SOURCE_UPLOAD = 'upload';
    public const SOURCE_POST = 'post';
    public const SOURCE_QUIZ = 'quiz';

    public function isEmpty(): bool
    {
        return !($this->submitted_on || $this->note);
    }

    public function getTask(): ModelTask
    {
        return ModelTask::createFromActiveRow($this->task, $this->mapper);
    }

    public function getContestant(): ModelContestant
    {
        // TODO why?
        return ModelContestant::createFromActiveRow($this->ref(DbNames::TAB_CONTESTANT_BASE, 'ct_id'), $this->mapper);
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
        if ($this->source != self::SOURCE_UPLOAD) {
            return false;
        }
        $now = time();
        $start = $this->getTask()->submit_start ? $this->getTask()->submit_start->getTimestamp() : 0;
        $deadline = $this->getTask()->submit_deadline ? $this->getTask()->submit_deadline->getTimestamp() : ($now + 1);
        return ($now <= $deadline) && ($now >= $start);
    }

    public function isQuiz(): bool
    {
        return $this->source === self::SOURCE_QUIZ;
    }
}

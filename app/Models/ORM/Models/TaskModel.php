<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\Utils\Utils;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Utils\Strings;

/**
 * @property-read int task_id
 * @property-read string label
 * @property-read string name_cs
 * @property-read string name_en
 * @property-read int contest_id
 * @property-read ContestModel contest
 * @property-read int year
 * @property-read int series
 * @property-read int tasknr
 * @property-read int points
 * @property-read \DateTimeInterface submit_start
 * @property-read \DateTimeInterface submit_deadline
 */
class TaskModel extends Model
{

    public function getFQName(): string
    {
        return sprintf('%s.%s %s', Utils::toRoman($this->series), $this->label, $this->name_cs);
    }

    public function getContributions(?TaskContributionType $type = null): TypedGroupedSelection
    {
        $contributions = $this->related(DbNames::TAB_TASK_CONTRIBUTION, 'task_id');
        if ($type) {
            $contributions->where('type', $type->value);
        }
        return $contributions;
    }

    public function getCategories(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_TASK_CATEGORY, 'task_id');
    }

    public function isForCategory(?ContestCategoryModel $category): bool
    {
        if (!$category) {
            return false;
        }
        return (bool)$this->getCategories()->where('contest_category_id', $category->contest_category_id)->fetch();
    }

    public function getContestYear(): ContestYearModel
    {
        return $this->contest->related(DbNames::TAB_CONTEST_YEAR, 'contest_id')->where('year', $this->year)->fetch();
    }

    public function webalizeLabel(): string
    {
        return Strings::webalize($this->label, null, false);
    }

    public function getTaskStats(): array
    {
        $count = 0;
        $sum = 0;
        /** @var SubmitModel $submit */
        foreach ($this->getSubmits() as $submit) {
            if (isset($submit->raw_points)) {
                $count++;
                $sum += $submit->raw_points;
            }
        }
        return ['solversCount' => $count, 'averagePoints' => $count ? ($sum / $count) : null];
    }

    public function __toArray(): array
    {
        return [
            'taskId' => $this->task_id,
            'series' => $this->series,
            'label' => $this->label,
            'name' => [
                'cs' => $this->name_cs,
                'en' => $this->name_en,
            ],
            'taskNumber' => $this->tasknr,
            'points' => $this->points,
        ];
    }

    public function getSubmits(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_SUBMIT, 'task_id');
    }

    public function getQuestions(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_SUBMIT_QUESTION, 'task_id');
    }

    public function isOpened(): bool
    {
        return $this->isAfterStart() && !$this->isAfterDeadline();
    }

    public function isAfterDeadline(): bool
    {
        if ($this->submit_deadline) {
            return time() > $this->submit_deadline->getTimestamp();
        }
        // if the deadline is not specified, consider task as opened, so default to false
        return false;
    }

    public function isAfterStart(): bool
    {
        if ($this->submit_start) {
            return time() > $this->submit_start->getTimestamp();
        }
        // if the deadline is not specified, consider task as opened, so default to true
        return true;
    }
}

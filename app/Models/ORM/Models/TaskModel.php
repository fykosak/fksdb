<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\Utils\Utils;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\Localization\LocalizedString;
use Nette\Utils\Strings;

/**
 * @property-read int $task_id
 * @property-read string $label
 * @property-read string $name_cs
 * @property-read string $name_en
 * @property-read int $contest_id
 * @property-read ContestModel $contest
 * @property-read int $year
 * @property-read int $series
 * @property-read int $tasknr
 * @property-read int $points
 * @property-read \DateTimeInterface $submit_start
 * @property-read \DateTimeInterface $submit_deadline
 */
final class TaskModel extends Model
{
    public function getName(): LocalizedString
    {
        return new LocalizedString(['cs' => $this->name_cs, 'en' => $this->name_en]);
    }

    public function getFullLabel(
        string $lang,
        bool $includeContest = false,
        bool $includeYear = false,
        bool $includeSeries = true
    ): string {
        $label = '';
        if ($includeContest) {
            $label .= $this->contest->name . ' ';
        }
        switch ($lang) {
            case 'cs':
                if ($includeYear) {
                    $label .= $this->year . '. ročník ';
                }
                if ($includeSeries) {
                    $label .= $this->series . '. série ';
                }
                return $label . $this->label . ' - ' . $this->name_cs;
            default:
                if ($includeYear) {
                    $label .= $this->year . Utils::ordinal($this->year) . ' year ';
                }
                if ($includeSeries) {
                    $label .= $this->series . Utils::ordinal($this->series) . ' series ';
                }
                return $label . $this->label . ' - ' . $this->name_en;
        }
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
        /** @var ContestYearModel $contestYear */
        $contestYear = $this->contest->related(DbNames::TAB_CONTEST_YEAR, 'contest_id')->where(
            'year',
            $this->year
        )->fetch();
        return $contestYear;
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
            'name' => $this->getName()->__serialize(),
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

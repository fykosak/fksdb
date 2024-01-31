<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\Utils\Utils;
use FKSDB\Modules\Core\Language;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\Localization\LocalizedString;
use Nette\Security\Resource;
use Nette\Utils\Strings;

/**
 * @property-read int $task_id
 * @property-read string $label
 * @property-read string|null $name_cs
 * @property-read string|null $name_en
 * @property-read LocalizedString $name
 * @property-read int $contest_id
 * @property-read ContestModel $contest
 * @property-read int $year
 * @property-read int $series
 * @property-read int|null $tasknr
 * @property-read int|null $points
 * @property-read \DateTimeInterface|null $submit_start
 * @property-read \DateTimeInterface|null $submit_deadline
 * @phpstan-type SerializedTaskModel array{
 *     taskId:int,
 *     series:int,
 *     label:string,
 *     name:array<string,string>,
 *     taskNumber:int|null,
 *     points:int|null,
 * }
 * @phpstan-type TaskStatsType array{solversCount:int,averagePoints:float|null}
 */
final class TaskModel extends Model implements Resource
{
    public const RESOURCE_ID = 'task';

    public function getFullLabel(
        Language $lang,
        bool $includeContest = false,
        bool $includeYear = false,
        bool $includeSeries = true
    ): string {
        $label = '';
        if ($includeContest) {
            $label .= $this->contest->name . ' ';
        }
        switch ($lang->value) {
            case 'cs':
                if ($includeYear) {
                    $label .= $this->year . '. ročník ';
                }
                if ($includeSeries) {
                    $label .= $this->series . '. série ';
                }
                break;
            default:
                if ($includeYear) {
                    $label .= $this->year . Utils::ordinal($this->year) . ' year ';
                }
                if ($includeSeries) {
                    $label .= $this->series . Utils::ordinal($this->series) . ' series ';
                }
        }
        return $label . $this->label . ' - ' . $this->name->getText('en');
    }

    /**
     * @phpstan-return TypedGroupedSelection<TaskContributionModel>
     */
    public function getContributions(?TaskContributionType $type = null): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<TaskContributionModel> $contributions */
        $contributions = $this->related(DbNames::TAB_TASK_CONTRIBUTION, 'task_id');
        if ($type) {
            $contributions->where('type', $type->value);
        }
        return $contributions;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TaskCategoryModel>
     */
    public function getCategories(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<TaskCategoryModel> $selection */
        $selection = $this->related(DbNames::TAB_TASK_CATEGORY, 'task_id');
        return $selection;
    }

    public function isForCategory(?ContestCategoryModel $category): bool
    {
        if (!$category) {
            return false;
        }
        return (bool)$this->getCategories()->where('contest_category_id', $category->contest_category_id)->fetch();
    }

    public function getContestYear(): ?ContestYearModel
    {
        /** @var ContestYearModel|null $contestYear */
        $contestYear = $this->contest->related(DbNames::TAB_CONTEST_YEAR, 'contest_id')->where(
            'year',
            $this->year
        )->fetch();
        return $contestYear;
    }

    /**
     * @return mixed
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {
        switch ($key) {
            case 'name':
                $value = new LocalizedString(['cs' => $this->name_cs, 'en' => $this->name_en]);
                break;
            default:
                $value = parent::__get($key);
        }
        return $value;
    }

    public function webalizeLabel(): string
    {
        return Strings::webalize($this->label, null, false);
    }

    /**
     * @phpstan-return TaskStatsType
     */
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

    /**
     * @phpstan-return SerializedTaskModel
     */
    public function __toArray(): array
    {
        return [
            'taskId' => $this->task_id,
            'series' => $this->series,
            'label' => $this->label,
            'name' => $this->name->__serialize(),
            'taskNumber' => $this->tasknr,
            'points' => $this->points,
        ];
    }

    /**
     * @phpstan-return TypedGroupedSelection<SubmitModel>
     */
    public function getSubmits(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<SubmitModel> $selection */
        $selection = $this->related(DbNames::TAB_SUBMIT, 'task_id');
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<SubmitQuestionModel>
     */
    public function getQuestions(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<SubmitQuestionModel> $selection */
        $selection = $this->related(DbNames::TAB_SUBMIT_QUESTION, 'task_id');
        return $selection;
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

    public function createUniqueKey(): string
    {
        return $this->contest_id . '-' . $this->year . '-' . $this->series . '-' . $this->label;
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}

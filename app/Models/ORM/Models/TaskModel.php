<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\Utils\Utils;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Utils\Strings;
use Fykosak\NetteORM\Model;

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

    /**
     * @return TaskStudyYearModel[] indexed by study_year
     */
    public function getStudyYears(): array
    {
        $studyYears = $this->related(DbNames::TAB_TASK_STUDY_YEAR, 'task_id');

        $result = [];
        /** @var TaskStudyYearModel $studyYear */
        foreach ($studyYears as $studyYear) {
            $result[$studyYear->study_year] = $studyYear;
        }
        return $result;
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
}

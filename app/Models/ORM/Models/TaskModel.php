<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\Utils\Utils;
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

    /**
     * @return TaskContributionModel[] indexed by contribution_id
     */
    public function getContributions(?TaskContributionType $type = null): array
    {
        $contributions = $this->related(DbNames::TAB_TASK_CONTRIBUTION, 'task_id');
        if ($type !== null) {
            $contributions->where(['type' => $type->value]);
        }

        $result = [];
        /** @var TaskContributionModel $contribution */
        foreach ($contributions as $contribution) {
            $result[$contribution->contribution_id] = $contribution;
        }
        return $result;
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

    public function webalizeLabel(): string
    {
        return Strings::webalize($this->label, null, false);
    }
}

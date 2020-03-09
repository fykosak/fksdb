<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use Nette\Utils\DateTime;
use Utils;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read integer series
 * @property-read string label
 * @property-read string name_cs
 * @property-read int task_id
 * @property-read int points
 * @property-read DateTime submit_deadline
 * @property-read DateTime submit_start
 */
class ModelTask extends AbstractModelSingle {

    /**
     * (Fully qualified) task name for use in GUI.
     *
     * @return string
     */
    public function getFQName(): string {
        return sprintf('%s.%s %s', Utils::toRoman($this->series), $this->label, $this->name_cs); //TODO i18n
    }

    /**
     * @param string $type ModelTaskContribution::TYPE_*
     * @return ModelTaskContribution[] indexed by contribution_id
     */
    public function getContributions($type = null): array {
        $contributions = $this->related(DbNames::TAB_TASK_CONTRIBUTION, 'task_id');
        if ($type !== null) {
            $contributions->where(['type' => $type]);
        }

        $result = [];
        foreach ($contributions as $contribution) {
            $contributionModel = ModelTaskContribution::createFromActiveRow($contribution);
            $result[$contributionModel->contribution_id] = $contributionModel;
        }
        return $result;
    }

    /**
     * @return ModelTaskStudyYear[] indexed by study_year
     */
    public function getStudyYears(): array {
        $studyYears = $this->related(DbNames::TAB_TASK_STUDY_YEAR, 'task_id');

        $result = [];
        foreach ($studyYears as $studyYear) {
            $studyYearModel = ModelTaskStudyYear::createFromActiveRow($studyYear);
            $result[$studyYearModel->study_year] = $studyYearModel;
        }
        return $result;
    }

}

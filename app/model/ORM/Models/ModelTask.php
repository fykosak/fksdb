<?php

namespace FKSDB\ORM\Models;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use Utils;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property integer series
 * @property string label
 * @property string name_cs
 * @property int task_id
 */
class ModelTask extends AbstractModelSingle {

    /**
     * (Fully qualified) task name for use in GUI.
     *
     * @return string
     */
    public function getFQName() {
        return sprintf('%s.%s %s', Utils::toRoman($this->series), $this->label, $this->name_cs); //TODO i18n
    }

    /**
     * @param string $type ModelTaskContribution::TYPE_*
     * @return ModelTaskContribution[] indexed by contribution_id
     */
    public function getContributions($type = null) {
        $contributions = $this->related(DbNames::TAB_TASK_CONTRIBUTION, 'task_id');
        if ($type !== null) {
            $contributions->where(['type' => $type]);
        }

        $result = [];
        foreach ($contributions as $contribution) {
            $contributionModel = ModelTaskContribution::createFromTableRow($contribution);
            $result[$contributionModel->contribution_id] = $contributionModel;
        }
        return $result;
    }

    /**
     * @return ModelTaskStudyYear[] indexed by study_year
     */
    public function getStudyYears() {
        $studyYears = $this->related(DbNames::TAB_TASK_STUDY_YEAR, 'task_id');

        $result = [];
        foreach ($studyYears as $studyYear) {
            $studyYearModel = ModelTaskStudyYear::createFromTableRow($studyYear);
            $result[$studyYearModel->study_year] = $studyYearModel;
        }
        return $result;
    }

}

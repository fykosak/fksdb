<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
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
     * @param enum $type ModelTaskContribution::TYPE_*
     * @return ModelTaskContribution[] indexed by contribution_id
     */
    public function getContributions($type = null) {
        $contributions = $this->related(DbNames::TAB_TASK_CONTRIBUTION, 'task_id');
        if ($type !== null) {
            $contributions->where(['type' => $type]);
        }

        $result = [];
        foreach ($contributions as $contribution) {
            $contribution = ModelTaskContribution::createFromTableRow($contribution);
            $result[$contribution->contribution_id] = $contribution;
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
            $studyYear = ModelTaskStudyYear::createFromTableRow($studyYear);
            $result[$studyYear->study_year] = $studyYear;
        }
        return $result;
    }

}

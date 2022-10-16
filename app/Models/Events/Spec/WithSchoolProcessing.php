<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Spec;

use FKSDB\Models\Events\Processing\AbstractProcessing;

abstract class WithSchoolProcessing extends AbstractProcessing
{
    protected function getSchoolValue(string $name): ?int
    {
        $schoolControls = $this->getControl("$name.person_id.person_history.school_id");
        $schoolControl = reset($schoolControls);
        if ($schoolControl) {
            $schoolControl->loadHttpData();
            return (int)$schoolControl->getValue();
        }
        return null;
    }

    public function getStudyYearValue(string $name): ?int
    {
        $studyYearControls = $this->getControl("$name.person_id.person_history.study_year");
        $studyYearControl = reset($studyYearControls);
        if ($studyYearControl) {
            $studyYearControl->loadHttpData();
            return $studyYearControl->getValue();
        }
        return null;
    }
}

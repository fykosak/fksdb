<?php

namespace FKSDB\Events\Spec;

use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Events\Processings\AbstractProcessing;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServiceSchool;
use FKSDB\YearCalculator;

/**
 * Class AbstractCategoryProcessing
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractCategoryProcessing extends AbstractProcessing {

    /**
     * @var YearCalculator
     */
    protected $yearCalculator;

    /**
     * @var ServiceSchool
     */
    protected $serviceSchool;

    /**
     * CategoryProcessing2 constructor.
     * @param YearCalculator $yearCalculator
     * @param ServiceSchool $serviceSchool
     */
    public function __construct(YearCalculator $yearCalculator, ServiceSchool $serviceSchool) {
        $this->yearCalculator = $yearCalculator;
        $this->serviceSchool = $serviceSchool;
    }

    protected function extractValues(Holder $holder): array {
        $acYear = $this->getAcYear($holder);

        $participants = [];
        foreach ($holder->getBaseHolders() as $name => $baseHolder) {
            if ($name == 'team') {
                continue;
            }
            $studyYearValue = $this->getStudyYearValue($name);
            $schoolValue = $this->getSchoolValue($name);
            if (!$studyYearValue && !$schoolValue && $this->isBaseReallyEmpty($name)) {
                continue;
            }

            /** @var ModelPerson $person */
            $person = $baseHolder->getModel()->getMainModel()->person;
            $history = $person->getHistory($acYear);

            $participants[] = [
                'school_id' => $schoolValue ?: ($history ? $history->school_id : null),
                'study_year' => $studyYearValue ?: ($history ? $history->study_year : null),
            ];
        }
        return $participants;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    protected function getSchoolValue(string $name) {
        $schoolControls = $this->getControl("$name.person_id.person_history.school_id");
        $schoolControl = reset($schoolControls);
        if ($schoolControl) {
            $schoolControl->loadHttpData();
            return $schoolControl->getValue();
        }
        return null;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getStudyYearValue(string $name) {
        $studyYearControls = $this->getControl("$name.person_id.person_history.study_year");
        $studyYearControl = reset($studyYearControls);
        if ($studyYearControl) {
            $studyYearControl->loadHttpData();
            return $studyYearControl->getValue();
        }
        return null;
    }

    protected function getAcYear(Holder $holder): int {
        $event = $holder->getPrimaryHolder()->getEvent();
        return $this->yearCalculator->getAcademicYear($event->getEventType()->getContest(), $event->year);
    }
}

<?php

namespace FKSDB\Events\Spec;

use FKSDB\Components\Forms\Factories\Events\IOptionsProvider;
use FKSDB\Events\Model\Holder\BaseHolder;
use FKSDB\Events\Model\Holder\Field;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Events\Processings\AbstractProcessing;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPersonHistory;
use FKSDB\ORM\Services\ServiceSchool;
use FKSDB\YearCalculator;

/**
 * Class AbstractCategoryProcessing
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractCategoryProcessing extends AbstractProcessing implements IOptionsProvider {

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

            $schoolValue = $this->getSchoolValue($name);
            $studyYearValue = $this->getStudyYearValue($name);

            if (!$schoolValue && !$studyYearValue) {
                if ($this->isBaseReallyEmpty($name)) {
                    continue;
                }
                $history = $this->getPersonHistory($baseHolder, $acYear);
                $schoolValue = $history->school_id;
                $studyYearValue = $history->study_year;
            }

            $participants[] = [
                'school_id' => $schoolValue,
                'study_year' => $studyYearValue,
            ];
        }

        return $participants;
    }

    /**
     * @param BaseHolder $baseHolder
     * @param int $acYear
     * @return ModelPersonHistory|null
     */
    private function getPersonHistory(BaseHolder $baseHolder, int $acYear) {
        /** @var ModelPerson $person */
        $person = $baseHolder->getModel()->getMainModel()->person;
        return $person->getHistory($acYear);
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

    /**
     * @param Field $field
     * @return array
     */
    public function getOptions(Field $field) {
        $results = [];
        foreach ([ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_A, ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_B, ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_C, ModelFyziklaniTeam::CATEGORY_ABROAD, ModelFyziklaniTeam::CATEGORY_OPEN] as $category) {
            $results[$category] = ModelFyziklaniTeam::mapCategoryToName($category);
        }
        return $results;
    }
}

<?php

namespace FKSDB\Model\Events\Spec;

use FKSDB\Components\Forms\Factories\Events\IOptionsProvider;
use FKSDB\Model\Events\Model\Holder\BaseHolder;
use FKSDB\Model\Events\Model\Holder\Field;
use FKSDB\Model\Events\Model\Holder\Holder;
use FKSDB\Model\Events\Processing\AbstractProcessing;
use FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Model\ORM\Models\ModelPerson;
use FKSDB\Model\ORM\Models\ModelPersonHistory;
use FKSDB\Model\ORM\Services\ServiceSchool;
use FKSDB\Model\YearCalculator;

/**
 * Class AbstractCategoryProcessing
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractCategoryProcessing extends AbstractProcessing implements IOptionsProvider {
    protected YearCalculator $yearCalculator;
    protected ServiceSchool $serviceSchool;

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

    private function getPersonHistory(BaseHolder $baseHolder, int $acYear): ?ModelPersonHistory {
        /** @var ModelPerson $person */
        $person = $baseHolder->getModel()->getMainModel()->getPerson();
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

    public function getOptions(Field $field): array {
        $results = [];
        foreach ([ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_A, ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_B, ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_C, ModelFyziklaniTeam::CATEGORY_ABROAD, ModelFyziklaniTeam::CATEGORY_OPEN] as $category) {
            $results[$category] = ModelFyziklaniTeam::mapCategoryToName($category);
        }
        return $results;
    }

    abstract protected function getCategory(array $competitors): string;
}

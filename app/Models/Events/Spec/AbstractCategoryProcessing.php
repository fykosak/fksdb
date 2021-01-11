<?php

namespace FKSDB\Models\Events\Spec;

use FKSDB\Components\Forms\Factories\Events\OptionsProvider;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Events\Processing\AbstractProcessing;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelPersonHistory;
use FKSDB\Models\ORM\Services\ServiceSchool;
use FKSDB\Models\YearCalculator;

/**
 * Class AbstractCategoryProcessing
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractCategoryProcessing extends AbstractProcessing implements OptionsProvider {

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

    protected function getSchoolValue(string $name): ?int {
        $schoolControls = $this->getControl("$name.person_id.person_history.school_id");
        $schoolControl = reset($schoolControls);
        if ($schoolControl) {
            $schoolControl->loadHttpData();
            return $schoolControl->getValue();
        }
        return null;
    }

    public function getStudyYearValue(string $name): ?int {
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

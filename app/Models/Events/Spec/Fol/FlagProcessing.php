<?php

namespace FKSDB\Models\Events\Spec\Fol;

use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Events\Spec\WithSchoolProcessing;
use Fykosak\Utils\Logging\Logger;
use FKSDB\Models\ORM\Models\ModelPersonHasFlag;
use FKSDB\Models\ORM\Services\ServiceSchool;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

class FlagProcessing extends WithSchoolProcessing {

    private ServiceSchool $serviceSchool;

    public function __construct(ServiceSchool $serviceSchool) {
        $this->serviceSchool = $serviceSchool;
    }

    protected function innerProcess(array $states, ArrayHash $values, Holder $holder, Logger $logger, ?Form $form): void {
        if (!isset($values['team'])) {
            return;
        }

        foreach ($holder->getBaseHolders() as $name => $baseHolder) {
            if ($name == 'team') {
                continue;
            }
            $formValues = [
                'school_id' => $this->getSchoolValue($name),
                'study_year' => $this->getStudyYearValue($name),
            ];

            if (!$formValues['school_id']) {
                if ($this->isBaseReallyEmpty($name)) {
                    continue;
                }

                $history = $baseHolder->getModel2()->mainModel->getPersonHistory();
                $participantData = [
                    'school_id' => $history->school_id,
                    'study_year' => $history->study_year,
                ];
            } else {
                $participantData = $formValues;
            }
            if (!($this->serviceSchool->isCzSkSchool($participantData['school_id']) && $this->isStudent($participantData['study_year']))) {
                /** @var ModelPersonHasFlag $personHasFlag */
                $personHasFlag = $values[$name]['person_id_1']['person_has_flag'];
                $personHasFlag->offsetUnset('spam_mff');
//                $a=$c;
//                $values[$name]['person_id_1']['person_has_flag']['spam_mff'] = null;
//                $a=$c;
                //unset($values[$name]['person_id_1']['person_has_flag']);
            }
        }
    }

    private function isStudent(?int $studyYear): bool {
        return !is_null($studyYear);
    }
}

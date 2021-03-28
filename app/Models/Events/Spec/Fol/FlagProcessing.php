<?php

namespace FKSDB\Models\Events\Spec\Fol;

use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Events\Processing\AbstractProcessing;
use FKSDB\Models\Logging\Logger;
use FKSDB\Models\ORM\Models\ModelPersonHasFlag;
use FKSDB\Models\ORM\Services\ServiceSchool;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

class FlagProcessing extends AbstractProcessing {

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
            /** @var BaseControl[][] $formControls */
            $formControls = [
                'school_id' => $this->getControl("$name.person_id.person_history.school_id"),
                'study_year' => $this->getControl("$name.person_id.person_history.study_year"),
            ];
            $formControls['school_id'] = reset($formControls['school_id']);
            $formControls['study_year'] = reset($formControls['study_year']);
            /** @var BaseControl[] $formControls */
            $formValues = [
                'school_id' => ($formControls['school_id'] ? $formControls['school_id']->getValue() : null),
                'study_year' => ($formControls['study_year'] ? $formControls['study_year']->getValue() : null),
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

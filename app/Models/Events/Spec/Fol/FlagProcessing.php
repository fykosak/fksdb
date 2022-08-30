<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Spec\Fol;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Spec\WithSchoolProcessing;
use Fykosak\Utils\Logging\Logger;
use FKSDB\Models\ORM\Models\PersonHasFlagModel;
use FKSDB\Models\ORM\Services\SchoolService;
use Nette\Utils\ArrayHash;

class FlagProcessing extends WithSchoolProcessing
{

    private SchoolService $schoolService;

    public function __construct(SchoolService $schoolService)
    {
        $this->schoolService = $schoolService;
    }

    protected function innerProcess(
        ArrayHash $values,
        BaseHolder $holder,
        Logger $logger
    ): void {
        if (!isset($values['team'])) {
            return;
        }
        if ($holder->name == 'team') {
            return;
        }
        $formValues = [
            'school_id' => $this->getSchoolValue($holder->name),
            'study_year' => $this->getStudyYearValue($holder->name),
        ];

        if (!$formValues['school_id']) {
            if ($this->isBaseReallyEmpty($holder->name)) {
                return;
            }

            $history = $holder->getModel()->mainModel->getPersonHistory();
            $participantData = [
                'school_id' => $history->school_id,
                'study_year' => $history->study_year,
            ];
        } else {
            $participantData = $formValues;
        }
        if (
            !($this->schoolService->isCzSkSchool($participantData['school_id'])
                && $this->isStudent($participantData['study_year']))
        ) {
            /** @var PersonHasFlagModel $personHasFlag */
            $personHasFlag = $values[$holder->name]['person_id_container']['person_has_flag'];
            $personHasFlag->offsetUnset('spam_mff');
//                $a=$c;
//                $values[$name]['person_id_1']['person_has_flag']['spam_mff'] = null;
//                $a=$c;
            //unset($values[$name]['person_id_1']['person_has_flag']);
        }
    }

    private function isStudent(?int $studyYear): bool
    {
        return !is_null($studyYear);
    }
}

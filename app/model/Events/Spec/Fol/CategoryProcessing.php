<?php

namespace FKSDB\Events\Spec\Fol;

use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\Holder\Field;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Events\Processings\AbstractProcessing;
use FKSDB\Components\Forms\Factories\Events\IOptionsProvider;
use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPersonHistory;
use FKSDB\ORM\Models\ModelRegion;
use FKSDB\ORM\Services\ServiceSchool;
use FKSDB\YearCalculator;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\Utils\ArrayHash;

/**
 * Class CategoryProcessing
 * *
 */
class CategoryProcessing extends AbstractProcessing implements IOptionsProvider {

    public const HIGH_SCHOOL_A = 'A';
    public const HIGH_SCHOOL_B = 'B';
    public const HIGH_SCHOOL_C = 'C';
    public const ABROAD = 'F';
    public const OPEN = 'O';

    private YearCalculator $yearCalculator;

    private ServiceSchool $serviceSchool;
    /** @var array */
    private $categoryNames;

    private int $rulesVersion;

    public function __construct(int $rulesVersion, YearCalculator $yearCalculator, ServiceSchool $serviceSchool) {
        $this->yearCalculator = $yearCalculator;
        $this->serviceSchool = $serviceSchool;

        if (!in_array($rulesVersion, [1, 2])) {
            throw new InvalidArgumentException(_('Not valid $rulesVersion.'));
        }
        $this->rulesVersion = $rulesVersion;

        if ($this->rulesVersion == 1) {
            $this->categoryNames = [
                self::HIGH_SCHOOL_A => sprintf(_('High school students %s'), 'A'),
                self::HIGH_SCHOOL_B => sprintf(_('High school students %s'), 'B'),
                self::HIGH_SCHOOL_C => sprintf(_('High school students %s'), 'C'),
                self::ABROAD => _('High school outside of CR/SR'),
                self::OPEN => _('Open'),
            ];
        } elseif ($this->rulesVersion == 2) {
            $this->categoryNames = [
                self::HIGH_SCHOOL_A => sprintf(_('High school students %s'), 'A'),
                self::HIGH_SCHOOL_B => sprintf(_('High school students %s'), 'B'),
                self::HIGH_SCHOOL_C => sprintf(_('High school students %s'), 'C'),
                self::OPEN => _('Open'),
            ];
        }
    }

    protected function innerProcess(array $states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, ?Form $form): void {
        if (!isset($values['team'])) {
            return;
        }

        $event = $holder->getPrimaryHolder()->getEvent();
        $contest = $event->getEventType()->contest;
        $year = $event->year;
        $acYear = $this->yearCalculator->getAcademicYear($contest, $year);

        $participants = [];
        foreach ($holder->getBaseHolders() as $name => $baseHolder) {
            if ($name == 'team') {
                continue;
            }
            $schoolControls = $this->getControl("$name.person_id.person_history.school_id");
            $schoolControl = reset($schoolControls);
            $studyYearControls = $this->getControl("$name.person_id.person_history.study_year");
            $studyYearControl = reset($studyYearControls);

            $schoolValue = null;
            if ($schoolControl) {
                $schoolControl->loadHttpData();
                $schoolValue = $schoolControl->getValue();
            }
            $studyYearValue = null;
            if ($studyYearControl) {
                $studyYearControl->loadHttpData();
                $studyYearValue = $studyYearControl->getValue();
            }

            if (!$schoolValue) {
                if ($this->isBaseReallyEmpty($name)) {
                    continue;
                }
                /** @var ModelPerson $person */
                $person = $baseHolder->getModel()->getMainModel()->person;
                /** @var ModelPersonHistory $history TODO type safe */
                $history = $person->related('person_history')->where('ac_year', $acYear)->fetch();
                $participantData = [
                    'school_id' => $history->school_id,
                    'study_year' => $history->study_year,
                ];
            } else {
                $participantData = [
                    'school_id' => $schoolValue,
                    'study_year' => $studyYearValue,
                ];
            }
            $participants[] = $participantData;
        }

        $result = $values['team']['category'] = $this->getCategory($participants);

        $original = $holder->getPrimaryHolder()->getModelState() != BaseMachine::STATE_INIT ? $holder->getPrimaryHolder()->getModel()->category : null;
        if ($original != $result) {
            $logger->log(new Message(sprintf(_('Team inserted to category %s.'), $this->categoryNames[$result]), ILogger::INFO));
        }
    }

    /*
     *   Open (staří odkudkoliv - pokazí to i jeden člen týmu)
     *   Zahraniční
     *   ČR - A - (3,4]
     *   ČR - B - (2,3] - max. 2 ze 4. ročníku
     *   ČR - C - [0,2] - nikdo ze 4. ročníku, max. 2 z 3 ročníku
     */
    private function getCategory(iterable $competitors): string {
        // init stats
        $olds = 0;
        $year = [0, 0, 0, 0, 0]; //0 - ZŠ, 1..4 - SŠ
        $abroad = 0;
        // calculate stats
        foreach ($competitors as $competitor) {
            if (!$competitor['school_id']) { // for future
                $olds += 1;
            } else {
                /** @var ModelRegion|false $country */
                $country = $this->serviceSchool->getTable()->select('address.region.country_iso')->where(['school_id' => $competitor['school_id']])->fetch();
                if (!in_array($country->country_iso, ['CZ', 'SK'])) {
                    $abroad += 1;
                }
            }

            if ($competitor['study_year'] === null) {
                $olds += 1;
            } elseif ($competitor['study_year'] >= 1 && $competitor['study_year'] <= 4) {
                $year[(int)$competitor['study_year']] += 1;
            } else {
                $year[0] += 1; // ZŠ
            }
        }
        // evaluate stats
        if ($olds > 0) {
            return self::OPEN;
        } elseif ($this->rulesVersion == 1 && $abroad > 0) {
            return self::ABROAD;
        } else { //Czech/Slovak highschoolers (or lower)
            $sum = 0;
            $cnt = 0;
            for ($y = 0; $y <= 4; ++$y) {
                $sum += $year[$y] * $y;
                $cnt += $year[$y];
            }
            $avg = $sum / $cnt;
            if ($avg <= 2 && $year[4] == 0 && $year[3] <= 2) {
                return self::HIGH_SCHOOL_C;
            } elseif ($avg <= 3 && $year[4] <= 2) {
                return self::HIGH_SCHOOL_B;
            } else {
                return self::HIGH_SCHOOL_A;
            }
        }
    }

    public function getOptions(Field $field): array {
        return $this->categoryNames;
    }
}

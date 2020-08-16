<?php

namespace FKSDB\Events\Spec\Fyziklani;

use FKSDB\Events\FormAdjustments\IFormAdjustment;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\ExpressionEvaluator;
use FKSDB\Events\Model\Holder\BaseHolder;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Services\ServicePersonHistory;
use Nette\Database\Context;
use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * More user friendly Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class TeamsPerSchool extends SchoolCheck implements IFormAdjustment {

    private Context $context;

    /** @var mixed */
    private $teamsPerSchool;

    private int $teamsPerSchoolValue;

    private ExpressionEvaluator $evaluator;

    /**
     * TeamsPerSchool constructor.
     * @param int $teamsPerSchool
     * @param ExpressionEvaluator $evaluator
     * @param Context $context
     * @param ServicePersonHistory $servicePersonHistory
     */
    public function __construct($teamsPerSchool, ExpressionEvaluator $evaluator, Context $context, ServicePersonHistory $servicePersonHistory) {
        parent::__construct($servicePersonHistory);
        $this->context = $context;
        $this->evaluator = $evaluator;
        $this->setTeamsPerSchool($teamsPerSchool);
    }

    public function getTeamsPerSchool(): int {
        if (!isset($this->teamsPerSchoolValue)) {
            $this->teamsPerSchoolValue = $this->evaluator->evaluate($this->teamsPerSchool, $this->getHolder());
        }
        return $this->teamsPerSchoolValue;
    }

    /**
     * @param int $teamsPerSchool
     * @return void
     */
    public function setTeamsPerSchool($teamsPerSchool): void {
        $this->teamsPerSchool = $teamsPerSchool;
    }

    protected function _adjust(Form $form, Machine $machine, Holder $holder): void {
        $this->setHolder($holder);
        $schoolControls = $this->getControl('p*.person_id.person_history.school_id');
        $personControls = $this->getControl('p*.person_id');

        $first = true;
        $msgMulti = sprintf(_('A school cannot have more than %d teams in the contest.'), $this->getTeamsPerSchool());
        foreach ($schoolControls as $control) {
            $control->addRule(function (IControl $control) use ($first, $schoolControls, $personControls) : bool {
                $schools = $this->getSchools($schoolControls, $personControls);
                return $this->checkMulti($first, $control, $schools);
            }, $msgMulti);
            $first = false;
        }
        $form->onValidate[] = function (Form $form) use ($schoolControls, $personControls, $msgMulti) : void {
            if ($form->isValid()) { // it means that all schools may have been disabled
                $schools = $this->getSchools($schoolControls, $personControls);
                if (!$this->checkMulti(true, null, $schools)) {
                    $form->addError($msgMulti);
                }
            }
        };
    }

    /** @var mixed */
    private $cache;

    private function checkMulti(bool $first, ?IControl $control, array $schools): bool {

        $team = $this->getHolder()->getPrimaryHolder()->getModel();
        $event = $this->getHolder()->getPrimaryHolder()->getEvent();
        $secondaryGroups = $this->getHolder()->getGroupedSecondaryHolders();
        $group = reset($secondaryGroups);
        $baseHolders = $group['holders'];
        /** @var BaseHolder $baseHolder */
        $baseHolder = reset($baseHolders);

        if (!$this->cache || $first) {
            /*
             * This may not be optimal.
             */
            $acYear = $event->getContest()->related('contest_year')->where('year', $event->year)->fetch()->ac_year;
            $result = $this->context->table(DbNames::TAB_EVENT_PARTICIPANT)
                ->select('person.person_history:school_id')
                ->select("GROUP_CONCAT(DISTINCT e_fyziklani_participant:e_fyziklani_team.name ORDER BY e_fyziklani_participant:e_fyziklani_team.created SEPARATOR ', ') AS teams")
                ->where($baseHolder->getEventIdColumn(), $event->getPrimary())
                ->where('person.person_history:ac_year', $acYear)
                ->where('person.person_history:school_id', $schools);

            //TODO filter by team status?
            if ($team && !$team->isNew()) {
                $result->where('NOT e_fyziklani_participant:e_fyziklani_team_id', $team->getPrimary());
            }

            $result->group('person.person_history:school_id', 'COUNT(DISTINCT e_fyziklani_participant:e_fyziklani_team.e_fyziklani_team_id) >= ' . $this->getTeamsPerSchool());

            $this->cache = $result->fetchPairs('school_id', 'teams');
        }
        if ($control) {
            $school = $control->getValue();
            if (isset($this->cache[$school])) {
                $control->addError(\sprintf(_('Registered teams from the same school %s.'), $this->cache[$school]));
            }
        }
        return count($this->cache) == 0;
    }

}

<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Spec\Fyziklani;

use FKSDB\Models\Events\Model\ExpressionEvaluator;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Services\PersonHistoryService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\Database\Explorer;
use Nette\Forms\Form;
use Nette\Forms\Control;

class TeamsPerSchool extends SchoolCheck
{

    private Explorer $explorer;
    /** @var callable|int */
    private $teamsPerSchool;
    private int $teamsPerSchoolValue;
    private ExpressionEvaluator $evaluator;

    /**
     * TeamsPerSchool constructor.
     * @param callable|int $teamsPerSchool
     */
    public function __construct(
        $teamsPerSchool,
        ExpressionEvaluator $evaluator,
        Explorer $explorer,
        PersonHistoryService $personHistoryService
    ) {
        parent::__construct($personHistoryService);
        $this->explorer = $explorer;
        $this->evaluator = $evaluator;
        $this->setTeamsPerSchool($teamsPerSchool);
    }

    public function getTeamsPerSchool(): int
    {
        if (!isset($this->teamsPerSchoolValue)) {
            $this->teamsPerSchoolValue = $this->evaluator->evaluate(
                $this->teamsPerSchool,
                $this->holder
            );
        }
        return $this->teamsPerSchoolValue;
    }

    /**
     * @param callable|int $teamsPerSchool
     */
    public function setTeamsPerSchool($teamsPerSchool): void
    {
        $this->teamsPerSchool = $teamsPerSchool;
    }

    /**
     * @param BaseHolder $holder
     */
    protected function innerAdjust(Form $form, ModelHolder $holder): void
    {
        $this->holder = $holder;
        $schoolControls = $this->getControl('p*.person_id.person_history.school_id');
        $personControls = $this->getControl('p*.person_id');

        $first = true;
        $msgMulti = sprintf(_('A school cannot have more than %d teams in the contest.'), $this->getTeamsPerSchool());
        foreach ($schoolControls as $control) {
            $control->addRule(function (Control $control) use ($first, $schoolControls, $personControls): bool {
                $schools = $this->getSchools($schoolControls, $personControls);
                return $this->checkMulti($first, $control, $schools);
            }, $msgMulti);
            $first = false;
        }
        $form->onValidate[] = function (Form $form) use ($schoolControls, $personControls, $msgMulti): void {
            // if ($form->isValid()) { // it means that all schools may have been disabled
            $schools = $this->getSchools($schoolControls, $personControls);
            if (!$this->checkMulti(true, null, $schools)) {
                $form->addError($msgMulti);
            }
            // }
        };
    }

    private array $cache;

    private function checkMulti(bool $first, ?Control $control, array $schools): bool
    {
        $team = $this->holder->getModel();

        if (!isset($this->cache) || $first) {
            /*
             * This may not be optimal.
             */
            $result = $this->explorer->table(DbNames::TAB_EVENT_PARTICIPANT)
                ->select('person.person_history:school_id')
                ->select(
                    "GROUP_CONCAT(DISTINCT e_fyziklani_participant:e_fyziklani_team.name 
                    ORDER BY e_fyziklani_participant:e_fyziklani_team.created SEPARATOR ', ') AS teams"
                )
                ->where('event_participant.event_id', $this->holder->event->getPrimary())
                ->where('person.person_history:ac_year', $this->holder->event->getContestYear()->ac_year)
                ->where('person.person_history:school_id', $schools);

            //TODO filter by team status?
            if ($team) {
                $result->where('NOT e_fyziklani_participant:e_fyziklani_team_id', $team->getPrimary(false));
            }

            $result->group(
                'person.person_history:school_id',
                'COUNT(DISTINCT e_fyziklani_participant:e_fyziklani_team.e_fyziklani_team_id) >= '
                . $this->getTeamsPerSchool()
            );

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

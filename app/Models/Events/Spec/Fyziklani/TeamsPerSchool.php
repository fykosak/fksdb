<?php

namespace FKSDB\Models\Events\Spec\Fyziklani;

use FKSDB\Models\Events\FormAdjustments\FormAdjustment;
use FKSDB\Models\Events\Model\ExpressionEvaluator;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Services\ServicePersonHistory;
use Nette\Database\Explorer;
use Nette\Forms\Control;
use Nette\Forms\Form;

class TeamsPerSchool extends SchoolCheck implements FormAdjustment
{

    private Explorer $explorer;
    /** @var callable|int */
    private $teamsPerSchool;
    private int $teamsPerSchoolValue;
    private ExpressionEvaluator $evaluator;
    private array $cache;

    /**
     * TeamsPerSchool constructor.
     * @param callable|int $teamsPerSchool
     * @param ExpressionEvaluator $evaluator
     * @param Explorer $explorer
     * @param ServicePersonHistory $servicePersonHistory
     */
    public function __construct(
        $teamsPerSchool,
        ExpressionEvaluator $evaluator,
        Explorer $explorer,
        ServicePersonHistory $servicePersonHistory
    ) {
        parent::__construct($servicePersonHistory);
        $this->explorer = $explorer;
        $this->evaluator = $evaluator;
        $this->setTeamsPerSchool($teamsPerSchool);
    }

    protected function innerAdjust(Form $form, Holder $holder): void
    {
        $this->setHolder($holder);
        $schoolControls = $this->getControl('p*.person_id.person_history.school_id');
        $personControls = $this->getControl('p*.person_id');

        $first = true;
        $msgMulti = sprintf(_('A school cannot have more than %d teams in the contest.'), $this->getTeamsPerSchool());
        foreach ($schoolControls as $control) {
            $control->addRule(
                function (Control $control) use ($first, $schoolControls, $personControls): bool {
                    $schools = $this->getSchools($schoolControls, $personControls);
                    return $this->checkMulti($first, $control, $schools);
                },
                $msgMulti
            );
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

    public function getTeamsPerSchool(): int
    {
        if (!isset($this->teamsPerSchoolValue)) {
            $this->teamsPerSchoolValue = $this->evaluator->evaluate($this->teamsPerSchool, $this->getHolder());
        }
        return $this->teamsPerSchoolValue;
    }

    /**
     * @param callable|int $teamsPerSchool
     * @return void
     */
    public function setTeamsPerSchool($teamsPerSchool): void
    {
        $this->teamsPerSchool = $teamsPerSchool;
    }

    private function checkMulti(bool $first, ?Control $control, array $schools): bool
    {
        $team = $this->getHolder()->getPrimaryHolder()->getModel2();
        $event = $this->getHolder()->getPrimaryHolder()->getEvent();
        $secondaryGroups = $this->getHolder()->getGroupedSecondaryHolders();
        $group = reset($secondaryGroups);
        $baseHolders = $group['holders'];
        /** @var BaseHolder $baseHolder */
        $baseHolder = reset($baseHolders);

        if (!isset($this->cache) || $first) {
            /*
             * This may not be optimal.
             */
            $result = $this->explorer->table(DbNames::TAB_EVENT_PARTICIPANT)
                ->select('person.person_history:school_id')
                ->select(
                    "GROUP_CONCAT(DISTINCT e_fyziklani_participant:e_fyziklani_team.name ORDER BY e_fyziklani_participant:e_fyziklani_team.created SEPARATOR ', ') AS teams"
                )
                ->where($baseHolder->getEventIdColumn(), $event->getPrimary())
                ->where('person.person_history:ac_year', $event->getContestYear()->ac_year)
                ->where('person.person_history:school_id', $schools);

            //TODO filter by team status?
            if ($team) {
                $result->where('NOT e_fyziklani_participant:e_fyziklani_team_id', $team->getPrimary(false));
            }

            $result->group(
                'person.person_history:school_id',
                'COUNT(DISTINCT e_fyziklani_participant:e_fyziklani_team.e_fyziklani_team_id) >= ' . $this->getTeamsPerSchool(
                )
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

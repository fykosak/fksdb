<?php

namespace Events\Spec\Fyziklani;

use DbNames;
use Events\FormAdjustments\IFormAdjustment;
use Events\Machine\Machine;
use Events\Model\ExpressionEvaluator;
use Events\Model\Holder\Holder;
use Nette\Database\Connection;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use ORM\IModel;
use ServicePersonHistory;

/**
 * More user friendly Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class TeamsPerSchool extends SchoolCheck implements IFormAdjustment {

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var mixed
     */
    private $teamsPerSchool;

    /**
     * @var int
     */
    private $teamsPerSchoolValue;

    /**
     * @var ExpressionEvaluator
     */
    private $evaluator;

    public function getTeamsPerSchool() {
        if ($this->teamsPerSchoolValue === null) {
            $this->teamsPerSchoolValue = $this->evaluator->evaluate($this->teamsPerSchool, $this->getHolder());
        }
        return $this->teamsPerSchoolValue;
    }

    public function setTeamsPerSchool($teamsPerSchool) {
        $this->teamsPerSchool = $teamsPerSchool;
    }

    function __construct($teamsPerSchool, ExpressionEvaluator $evaluator, Connection $connection, ServicePersonHistory $servicePersonHistory) {
        parent::__construct($servicePersonHistory);
        $this->connection = $connection;
        $this->evaluator = $evaluator;
        $this->setTeamsPerSchool($teamsPerSchool);
    }

    protected function _adjust(Form $form, Machine $machine, Holder $holder) {
        $this->setHolder($holder);
        $schoolControls = $this->getControl('p*.person_id.person_history.school_id');
        $personControls = $this->getControl('p*.person_id');

        $first = true;
        $msgMulti = sprintf(_('Škola nemůže mít v soutěži více týmů než %d.'), $this->getTeamsPerSchool());
        foreach ($schoolControls as $control) {
            $control->addRule(function(IControl $control) use ($first, $schoolControls, $personControls) {
                        $schools = $this->getSchools($schoolControls, $personControls);
                        return $this->checkMulti($first, $control, $schools);
                    }, $msgMulti);
            $first = false;
        }
        $form->onValidate[] = function(Form $form) use($schoolControls, $personControls, $msgMulti) {
                    if ($form->isValid()) { // it means that all schools may have been disabled
                        $schools = $this->getSchools($schoolControls, $personControls);
                        if (!$this->checkMulti(true, NULL, $schools)) {
                            $form->addError($msgMulti);
                        }
                    }
                };
    }

    private $cache;

    private function checkMulti($first, $control, $schools) {
        $holder = $this->getHolder();
        $team = $holder->getPrimaryHolder()->getModel();
        $event = $holder->getPrimaryHolder()->getEvent();
        $secondaryGroups = $holder->getGroupedSecondaryHolders();
        $group = reset($secondaryGroups);
        $baseHolders = $group['holders'];
        $baseHolder = reset($baseHolders);

        if (!$this->cache || $first) {
            /*
             * This may not be optimal.
             */
            $acYear = $event->event_type->contest->related('contest_year')->where('year', $event->year)->fetch()->ac_year;
            $result = $this->connection->table(DbNames::TAB_EVENT_PARTICIPANT)
                    ->select('person.person_history:school_id')
                    ->select("GROUP_CONCAT(DISTINCT e_fyziklani_participant:e_fyziklani_team.name ORDER BY e_fyziklani_participant:e_fyziklani_team.created SEPARATOR ', ') AS teams")
                    ->where($baseHolder->getEventId(), $event->getPrimary())
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
                $control->addError($this->cache[$school]);
            }
        }
        return count($this->cache) == 0;
    }

}


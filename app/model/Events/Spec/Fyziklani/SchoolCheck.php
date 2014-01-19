<?php

namespace Events\Spec\Fyziklani;

use DbNames;
use Events\FormAdjustments\AbstractAdjustment;
use Events\FormAdjustments\IFormAdjustment;
use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Nette\Database\Connection;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use ORM\IModel;

/**
 * More user friendly Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class SchoolCheck extends AbstractAdjustment implements IFormAdjustment {

    const SCHOOLS_IN_TEAM = 2;
    const TEAMS_PER_SCHOOL = 2;

    /**
     * @var Connection
     */
    private $connection;

    function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    protected function _adjust(Form $form, Machine $machine, Holder $holder) {
        $controls = $this->getControl('p*.person_id.person_history.school_id');

        $that = $this;
        $first = true;
        $msgMixture = sprintf(_('V týmu můžou být soutežící nejvýše z %d škol.'), self::SCHOOLS_IN_TEAM);
        $msgMulti = sprintf(_('Škola nemůže mít v soutěži více týmů než %d.'), self::TEAMS_PER_SCHOOL);
        foreach ($controls as $control) {
            $control->addRule(function(IControl $control) use ($that, $controls, $form, $msgMixture) {
                        $schools = $that->getSchools($controls);
                        if (!$this->checkMixture($schools)) {
                            $form->addError($msgMixture);
                            return false;
                        }
                        return true;
                    }, $msgMixture);
            $control->addRule(function(IControl $control) use ($first, $that, $controls, $holder) {
                        $schools = $that->getSchools($controls);
                        return $this->checkMulti($first, $control, $schools, $holder, $holder->getPrimaryHolder()->getModel());
                    }, $msgMulti);
            $first = false;
        }
    }

    private function checkMixture($schools) {
        return count(array_unique($schools)) <= self::SCHOOLS_IN_TEAM;
    }

    private $cache;

    private function checkMulti($first, $control, $schools, Holder $holder, IModel $team = null) {
        $event = $holder->getEvent();
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
                    ->where('person.person_history:school_id', $schools)
                    ->where('person.person_history:ac_year', $acYear);
            //TODO filter by team status?
            if ($team && !$team->isNew()) {
                $result->where('NOT e_fyziklani_participant.e_fyziklani_team_id', $team->getPrimary());
            }

            $result->group('person.person_history:school_id', 'COUNT(DISTINCT e_fyziklani_participant:e_fyziklani_team.e_fyziklani_team_id) >= ' . self::TEAMS_PER_SCHOOL);

            $this->cache = $result->fetchPairs('school_id', 'teams');
        }
        $school = $control->getValue();
        if (isset($this->cache[$school])) {
            $control->addError($this->cache[$school]);
            return false;
        } else {
            return true;
        }
    }

    private function getSchools($controls) {
        $result = array();
        foreach ($controls as $control) {
            if ($control->getValue()) {
                $result[] = $control->getValue();
            }
        }
        return $result;
    }

}


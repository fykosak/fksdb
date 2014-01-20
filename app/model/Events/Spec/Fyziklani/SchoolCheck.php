<?php

namespace Events\Spec\Fyziklani;

use DbNames;
use Events\FormAdjustments\AbstractAdjustment;
use Events\FormAdjustments\IFormAdjustment;
use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Nette\Database\Connection;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use ORM\IModel;
use ServicePersonHistory;

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

    /**
     * @var ServicePersonHistory
     */
    private $servicePersonHistory;

    /**
     * @var Holder
     */
    private $holder;

    function __construct(Connection $connection, ServicePersonHistory $servicePersonHistory) {
        $this->connection = $connection;
        $this->servicePersonHistory = $servicePersonHistory;
    }

    protected function _adjust(Form $form, Machine $machine, Holder $holder) {
        $this->holder = $holder;
        $schoolControls = $this->getControl('p*.person_id.person_history.school_id');
        $personControls = $this->getControl('p*.person_id');

        $that = $this;
        $first = true;
        $msgMixture = sprintf(_('V týmu můžou být soutežící nejvýše z %d škol.'), self::SCHOOLS_IN_TEAM);
        $msgMulti = sprintf(_('Škola nemůže mít v soutěži více týmů než %d.'), self::TEAMS_PER_SCHOOL);
        foreach ($schoolControls as $control) {
            $control->addRule(function(IControl $control) use ($that, $schoolControls, $personControls, $form, $msgMixture) {
                        $schools = $that->getSchools($schoolControls, $personControls);
                        if (!$that->checkMixture($schools)) {
                            $form->addError($msgMixture);
                            return false;
                        }
                        return true;
                    }, $msgMixture);
            $control->addRule(function(IControl $control) use ($first, $that, $schoolControls, $personControls, $holder) {
                        $schools = $that->getSchools($schoolControls, $personControls);
                        return $that->checkMulti($first, $control, $schools, $holder);
                    }, $msgMulti);
            $first = false;
        }
        $form->onValidate[] = function(Form $form) use($that, $schoolControls, $personControls, $msgMixture, $msgMulti) {
                    if ($form->isValid()) { // it means that all schools may have been disabled
                        $schools = $that->getSchools($schoolControls, $personControls);
                        if (!$that->checkMixture($schools)) {
                            $form->addError($msgMixture);
                        }
                        if (!$that->checkMulti(true, NULL, $schools, $that->holder)) {
                            $form->addError($msgMulti);
                        }
                    }
                };
    }

    private function checkMixture($schools) {
        return count(array_unique($schools)) <= self::SCHOOLS_IN_TEAM;
    }

    private $cache;

    private function checkMulti($first, $control, $schools, Holder $holder, IModel $team = null) {
        $team = $holder->getPrimaryHolder()->getModel();
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
                    ->where('person.person_history:ac_year', $acYear)
                    ->where('person.person_history:school_id', $schools);

            //TODO filter by team status?
            if ($team && !$team->isNew()) {
                $result->where('NOT e_fyziklani_participant:e_fyziklani_team_id', $team->getPrimary());
            }

            $result->group('person.person_history:school_id', 'COUNT(DISTINCT e_fyziklani_participant:e_fyziklani_team.e_fyziklani_team_id) >= ' . self::TEAMS_PER_SCHOOL);

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

    private function getSchools($schoolControls, $personControls) {
        $personIds = array_map(function(BaseControl $control) {
                    return $control->getValue();
                }, $personControls);
        $schools = $this->servicePersonHistory->getTable()
                ->where('person_id', $personIds)
                ->where('ac_year', $this->holder->getEvent()->getAcYear())
                ->fetchPairs('person_id', 'school_id');

        $result = array();
        foreach ($schoolControls as $key => $control) {
            if ($control->getValue()) {
                $result[] = $control->getValue();
            } else if ($schoolId = $personControls[$key]->getValue()) { // intentionally =
                $result[] = $schools[$schoolId];
            }
        }
        return $result;
    }

}


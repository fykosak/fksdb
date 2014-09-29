<?php

namespace Events\Spec\Dsef;

use DbNames;
use Events\Machine\BaseMachine;
use Events\Model\Holder\Field;
use FKSDB\Components\Forms\Factories\Events\IOptionsProvider;
use Nette\Object;
use ORM\ServicesMulti\Events\ServiceMDsefParticipant;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GroupOptions
 *
 * @author michal
 */
class GroupOptions extends Object implements IOptionsProvider {

    const PARAM_GROUPS = 'groups';

    /**
     * @var ServiceMDsefParticipant
     */
    private $serviceMParticipant;
    private $includeStates;
    private $excludeStates;

    /**
     * 
     * @param ServiceMDsefParticipant $serviceMParticipant
     * @param string|array $includeStates any state or array of state
     * @param string|array $excludeStates any state or array of state
     */
    function __construct(ServiceMDsefParticipant $serviceMParticipant, $includeStates = BaseMachine::STATE_ANY, $excludeStates = array('cancelled')) {
        $this->serviceMParticipant = $serviceMParticipant;
        $this->includeStates = $includeStates;
        $this->excludeStates = $excludeStates;
    }

    private function transformGroups($groups) {
        $result = array();
        foreach ($groups as $name => $capacity) {
            $result[] = array(
                'label' => $name,
                'capacity' => $capacity
            );
        }
        return $result;
    }

    public function getOptions(Field $field) {
        $baseHolder = $field->getBaseHolder();
        $event = $baseHolder->getEvent();
        $application = $baseHolder->getModel();
        $groups = $this->transformGroups($baseHolder->getParameter(self::PARAM_GROUPS));

        $selection = $this->serviceMParticipant->getTable()
                ->getConnection()->table(DbNames::TAB_E_DSEF_PARTICIPANT)
                ->select('e_dsef_group_id, count(event_participant.event_participant_id) AS occupied')
                ->group('e_dsef_group_id')
                ->where('event_id', $event->event_id)
                ->where('NOT event_participant.event_participant_id', $application->getPrimary(false));
        if ($this->includeStates !== BaseMachine::STATE_ANY) {
            $selection->where('event_participant.status', $this->includeStates);
        }
        if ($this->excludeStates !== BaseMachine::STATE_ANY) {
            $selection->where('NOT event_participant.status', $this->excludeStates);
        } else {
            $selection->where('1=0');
        }
        $groupOccupied = $selection->fetchPairs('e_dsef_group_id', 'occupied');

        $selfGroup = $application->e_dsef_group_id;
        $result = array();
        foreach ($groups as $key => $data) {
            $occupied = isset($groupOccupied[$key]) ? $groupOccupied[$key] : 0;
            if ($data['capacity'] > $occupied) {
                $remains = $data['capacity'] - $occupied;
                if ($selfGroup === $key) {
                    $remains -= 1;
                }
                $info = sprintf(_('(%d volných míst)'), $remains);
                $result[$key] = $data['label'] . ' ' . $info;
            }
        }

        return $result;
    }

}

?>

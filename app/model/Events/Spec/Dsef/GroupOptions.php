<?php

namespace Events\Spec\Dsef;

use DbNames;
use Events\Machine\BaseMachine;
use Events\Model\Holder\Field;
use FKSDB\Components\Forms\Factories\Events\IOptionsProvider;
use Nette\Object;
use ORM\Services\Events\ServiceDsefGroup;
use ORM\ServicesMulti\Events\ServiceMDsefParticipant;

/**
 *
 * @author michal
 */
class GroupOptions extends Object implements IOptionsProvider {

    /**
     * @var ServiceMDsefParticipant
     */
    private $serviceMParticipant;

    /**
     * @var ServiceDsefGroup
     */
    private $serviceDsefGroup;
    private $includeStates;
    private $excludeStates;

    /**
     * @var array  eventId => groups cache
     */
    private $groups = [];

    /**
     * @note In NEON instatiate as GroupOptions(..., ['state1'],['state1', 'state2']).
     *
     * @param ServiceMDsefParticipant $serviceMParticipant
     * @param ServiceDsefGroup $serviceDsefGroup
     * @param string|array $includeStates any state or array of state
     * @param string|array $excludeStates any state or array of state
     */
    function __construct(ServiceMDsefParticipant $serviceMParticipant, ServiceDsefGroup $serviceDsefGroup, $includeStates = BaseMachine::STATE_ANY, $excludeStates = ['cancelled']) {
        $this->includeStates = $includeStates;
        $this->excludeStates = $excludeStates;
        $this->serviceMParticipant = $serviceMParticipant;
        $this->serviceDsefGroup = $serviceDsefGroup;
    }

    private function transformGroups($groups) {
        $result = [];
        foreach ($groups as $name => $capacity) {
            $result[] = [
                'label' => $name,
                'capacity' => $capacity
            ];
        }
        return $result;
    }

    private function getGroups($eventId) {
        if (!isset($this->groups[$eventId])) {
            $this->groups[$eventId] = $this->serviceDsefGroup->getTable()
                    ->select('*')
                    ->where('event_id', $eventId)
                    ->fetchPairs('e_dsef_group_id');
        }
        return $this->groups[$eventId];
    }

    public function getOptions(Field $field) {
        $baseHolder = $field->getBaseHolder();
        $event = $baseHolder->getEvent();
        $application = $baseHolder->getModel();
        $groups = $this->getGroups($event->getPrimary());

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
        $result = [];
        foreach ($groups as $key => $group) {
            $occupied = isset($groupOccupied[$key]) ? $groupOccupied[$key] : 0;
            if ($group->capacity > $occupied) {
                $remains = $group->capacity - $occupied;
                if ($selfGroup === $key) {
                    $remains -= 1;
                }
                $info = sprintf(_('(%d volných míst)'), $remains);
                $result[$key] = $group->name . ' ' . $info;
            }
        }

        return $result;
    }

}

?>

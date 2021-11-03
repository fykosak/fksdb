<?php

namespace FKSDB\Models\Events\Spec\Dsef;

use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Events\ModelDsefGroup;
use FKSDB\Models\ORM\Models\Events\ModelDsefParticipant;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\Events\ServiceDsefGroup;
use FKSDB\Models\Transitions\Machine\Machine;
use Nette\SmartObject;
use FKSDB\Components\Forms\Factories\Events\OptionsProvider;
use FKSDB\Models\ORM\ServicesMulti\Events\ServiceMDsefParticipant;

/**
 * @deprecated
 */
class GroupOptions implements OptionsProvider {

    use SmartObject;

    private ServiceMDsefParticipant $serviceMParticipant;
    private ServiceDsefGroup $serviceDsefGroup;
    /** @var string|string[] */
    private $includeStates;
    /** @var string|string[] */
    private $excludeStates;

    private array $groups = [];

    /**
     * @note In NEON instatiate as GroupOptions(..., ['state1'],['state1', 'state2']).
     *
     * @param string|array $includeStates any state or array of state
     * @param string|array $excludeStates any state or array of state
     */
    public function __construct(
        ServiceMDsefParticipant $serviceMParticipant,
        ServiceDsefGroup $serviceDsefGroup,
        $includeStates = Machine::STATE_ANY,
        $excludeStates = ['cancelled']
    ) {
        $this->includeStates = $includeStates;
        $this->excludeStates = $excludeStates;
        $this->serviceMParticipant = $serviceMParticipant;
        $this->serviceDsefGroup = $serviceDsefGroup;
    }

    private function transformGroups(iterable $groups): array {
        $result = [];
        foreach ($groups as $name => $capacity) {
            $result[] = [
                'label' => $name,
                'capacity' => $capacity,
            ];
        }
        return $result;
    }

    /**
     * @return ModelDsefGroup[]
     */
    private function getGroups(ModelEvent $event): array {
        return $event->related(DbNames::TAB_E_DSEF_GROUP)->fetchPairs('e_dsef_group_id');
    }

    public function getOptions(Field $field): array {
        $baseHolder = $field->getBaseHolder();
        $event = $baseHolder->getEvent();
        /** @var ModelDsefParticipant $model */
        $model = $baseHolder->getModel2();
        $groups = $this->getGroups($event);

        $selection = $this->serviceMParticipant->mainService->explorer->table(DbNames::TAB_E_DSEF_PARTICIPANT)
            ->select('e_dsef_group_id, count(event_participant.event_participant_id) AS occupied')
            ->group('e_dsef_group_id')
            ->where('event_id', $event->event_id)
            ->where('NOT event_participant.event_participant_id', $model ? $model->getPrimary(false) : null);
        if ($this->includeStates !== Machine::STATE_ANY) {
            $selection->where('event_participant.status', $this->includeStates);
        }
        if ($this->excludeStates !== Machine::STATE_ANY) {
            $selection->where('NOT event_participant.status', $this->excludeStates);
        } else {
            $selection->where('1=0');
        }
        $groupOccupied = $selection->fetchPairs('e_dsef_group_id', 'occupied');

        $selfGroup = $model ? $model->e_dsef_group_id : $baseHolder->data['e_dsef_group_id'];
        $result = [];
        foreach ($groups as $key => $group) {
            $occupied = $groupOccupied[$key] ?? 0;
            if ($group->capacity > $occupied) {
                $remains = $group->capacity - $occupied;
                if ($selfGroup === $key) {
                    $remains -= 1;
                }
                $info = sprintf(_('(%d vacancies)'), $remains);
                $result[$key] = $group->name . ' ' . $info;
            }
        }
        return $result;
    }
}

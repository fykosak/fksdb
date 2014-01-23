<?php

namespace Events\Spec\Fyziklani;

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

    function __construct(ServiceMDsefParticipant $serviceMParticipant) {
        $this->serviceMParticipant = $serviceMParticipant;
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
        $holder = $field->getBaseHolder()->getHolder();
        $event = $holder->getEvent();
        $application = $field->getBaseHolder()->getModel();
        $groups = $this->transformGroups($holder->getParameter(self::PARAM_GROUPS));

        $groupOccupied = $this->serviceMParticipant->getTable()
                ->getConnection()->table(\DbNames::TAB_E_DSEF_PARTICIPANT)
                ->select('e_dsef_group_id, count(event_participant.event_participant_id) AS occupied')
                ->group('e_dsef_group_id')
                ->where('event_id', $event->event_id)
                ->where('NOT event_participant.event_participant_id', $application->getPrimary(false))
                ->fetchPairs('e_dsef_group_id', 'occupied');

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

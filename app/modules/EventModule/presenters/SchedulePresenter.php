<?php

namespace EventModule;

use FKSDB\Components\Controls\Schedule\GroupControl;
use FKSDB\Components\Controls\Schedule\ItemControl;
use FKSDB\Components\Factories\ScheduleFactory;
use FKSDB\Components\Grids\Schedule\GroupsGrid;
use FKSDB\Components\Grids\Schedule\ItemsGrid;
use FKSDB\Components\Grids\Schedule\PersonsGrid;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\ORM\Services\Schedule\ServiceScheduleGroup;
use FKSDB\ORM\Services\Schedule\ServiceScheduleItem;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\Connection;
use Nette\InvalidArgumentException;

/**
 * Class SchedulePresenter
 * @package EventModule
 */
class SchedulePresenter extends BasePresenter {
    /**
     * @var int
     * @persistent
     */
    public $id;
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var ScheduleFactory
     */
    private $scheduleFactory;
    /**
     * @var ModelScheduleGroup
     */
    private $group;
    /**
     * @var ModelScheduleItem
     */
    private $item;
    /**
     * @var ServiceScheduleGroup
     */
    private $serviceScheduleGroup;
    /**
     * @var ServiceScheduleItem
     */
    private $serviceScheduleItem;

    /**
     * @param Connection $connection
     */
    public function injectConnection(Connection $connection) {
        $this->connection = $connection;
    }

    /**
     * @param ServiceScheduleGroup $serviceScheduleGroup
     */
    public function injectServiceScheduleGroup(ServiceScheduleGroup $serviceScheduleGroup) {
        $this->serviceScheduleGroup = $serviceScheduleGroup;
    }

    /**
     * @param ServiceScheduleItem $serviceScheduleItem
     */
    public function injectServiceScheduleItem(ServiceScheduleItem $serviceScheduleItem) {
        $this->serviceScheduleItem = $serviceScheduleItem;
    }

    /**
     * @param ScheduleFactory $scheduleFactory
     */
    public function injectScheduleComponentFactory(ScheduleFactory $scheduleFactory) {
        $this->scheduleFactory = $scheduleFactory;
    }

    /**
     * @deprecated
     */
    public function titleDefault() {
        $this->setTitle(\sprintf(_('Schedule')));
        $this->setIcon('fa fa-calendar-check-o');
    }

    public function titleGroups() {
        $this->setTitle(\sprintf(_('Schedule groups')));
        $this->setIcon('fa fa-calendar-check-o');
    }

    public function titleItem() {
        $this->setTitle(\sprintf(_('Schedule item #%d'), $this->item->schedule_item_id));
        $this->setIcon('fa fa-calendar-check-o');
    }

    public function titleGroup() {
        $this->setTitle(\sprintf(_('Schedule group #%d'), $this->group->schedule_group_id));
        $this->setIcon('fa fa-calendar-check-o');
    }

    /**
     * @return bool
     * @throws AbortException
     * @deprecated
     * @throws BadRequestException
     */
    protected function hasEventSchedule() {
        try {
            $this->getEvent()->getParameter('schedule');
        } catch (InvalidArgumentException $e) {
            return false;
        }
        return true;
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     * @deprecated
     */
    public function authorizedDefault() {

        if ($this->hasEventSchedule()) {
            return $this->setAuthorized($this->eventIsAllowed('event.schedule', 'default'));
        }
        return $this->setAuthorized(false);
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     * @deprecated
     */
    public function renderDefault() {
        $query = $this->connection->query('SELECT p.name,p.person_id,apps.type, group_concat(DISTINCT apps.team separator \', \') AS `team`,schedule
FROM v_person p
right join (
  select teacher_id as person_id,\'teacher\' as type, event_id, name as team, teacher_schedule as schedule
  FROM e_fyziklani_team eft
  WHERE eft.status != \'cancelled\'
  UNION ALL
  SELECT person_id, \'participant\' AS type, ep.event_id, eftp.name, ep.schedule
  FROM event_participant ep
  right join e_fyziklani_participant efp on efp.event_participant_id = ep.event_participant_id
  LEFT JOIN e_fyziklani_team eftp on eftp.e_fyziklani_team_id = efp.e_fyziklani_team_id
  WHERE eftp.status != \'cancelled\' 
) apps ON apps.person_id = p.person_id
LEFT JOIN event e ON e.event_id = apps.event_id
WHERE e.event_id=?
GROUP BY p.person_id,type,schedule', $this->getEvent()->event_id)->fetchAll();
        $schedule = $this->getEvent()->getParameter('schedule');
        $results = [];
        $stats = [];
        foreach ($query as $row) {
            if ($row->schedule) {
                $innerSchedule = json_decode($row->schedule);
                $results[] = [
                    'name' => $row->name,
                    'schedule' => $innerSchedule,
                    'person_id' => $row->person_id,
                    'type' => $row->type,
                    'team' => $row->team,
                ];
                foreach ($innerSchedule as $key => $item) {
                    if (!isset($stats[$key])) {
                        $stats[$key] = [];
                    }
                    if (!isset($stats[$key][$item])) {
                        $stats[$key][$item] = 0;
                    }
                    $stats[$key][$item] += 1;

                }
            };

        }

        $this->template->participants = $results;
        $this->template->schedule = $schedule;
        $this->template->stats = $stats;
    }

    /**
     * @param $id
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws AbortException
     */
    public function actionGroup($id) {
        if (!$this->group) {
            $row = $this->serviceScheduleGroup->findByPrimary($id);
            if (!$row) {
                throw new BadRequestException();
            }
            $this->group = ModelScheduleGroup::createFromActiveRow($row);
        }
        if ($this->group->getEvent()->event_id !== $this->getEvent()->event_id) {
            throw new ForbiddenRequestException('Schedule group does not belong to this event');
        }
        /**
         * @var ItemsGrid $component
         */
        $component = $this->getComponent('itemsGrid');
        $component->setGroup($this->getGroup());
        /**
         * @var GroupControl $groupControl
         */
        $groupControl = $this->getComponent('groupControl');
        $groupControl->setGroup($this->getGroup());
    }

    /**
     * @param $id
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws AbortException
     */
    public function actionItem($id) {
        if (!$this->item) {
            $row = $this->serviceScheduleItem->findByPrimary($id);
            if (!$row) {
                throw new BadRequestException();
            }
            $this->item = ModelScheduleItem::createFromActiveRow($row);
        }
        if ($this->item->getGroup()->getEvent()->event_id !== $this->getEvent()->event_id) {
            throw new ForbiddenRequestException('Schedule item does not belong to this event');
        }
        /**
         * @var PersonsGrid $component
         */
        $component = $this->getComponent('personsGrid');
        $component->setItem($this->getItem());
        /**
         * @var GroupControl $groupControl
         */
        $groupControl = $this->getComponent('groupControl');
        $groupControl->setGroup($this->getItem()->getGroup());
        /**
         * @var ItemControl $itemControl
         */
        $itemControl = $this->getComponent('itemControl');
        $itemControl->setItem($this->getItem());
    }

    /**
     * @return ModelScheduleGroup
     */
    private function getGroup(): ModelScheduleGroup {
        return $this->group;
    }

    /**
     * @return ModelScheduleItem
     */
    private function getItem(): ModelScheduleItem {
        return $this->item;
    }

    /**
     *
     */
    public function renderItem() {
        $this->template->item = $this->getItem();
        $this->template->group = $this->getItem()->getGroup();
    }
    /* *************** COMPONENTS ****************/
    /**
     * @return GroupsGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentGroupsGrid(): GroupsGrid {
        return $this->scheduleFactory->createGroupsGrid($this->getEvent());
    }

    /**
     * @return ItemsGrid
     */
    public function createComponentItemsGrid(): ItemsGrid {
        return $this->scheduleFactory->createItemsGrid();
    }

    /**
     * @return PersonsGrid
     */
    public function createComponentPersonsGrid(): PersonsGrid {
        return $this->scheduleFactory->createPersonsGrid();
    }

    /**
     * @return GroupControl
     */
    public function createComponentGroupControl(): GroupControl {
        return $this->scheduleFactory->createGroupControl();
    }

    /**
     * @return ItemControl
     */
    public function createComponentItemControl(): ItemControl {
        return $this->scheduleFactory->createItemControl();
    }
}

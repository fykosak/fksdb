<?php

namespace EventModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Schedule\GroupControl;
use FKSDB\Components\Controls\Schedule\ItemControl;
use FKSDB\Components\Factories\ScheduleFactory;
use FKSDB\Components\Grids\Schedule\AllPersonsGrid;
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
use function sprintf;

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

    public function titleGroups() {
        $this->setTitle(sprintf(_('Schedule groups')));
        $this->setIcon('fa fa-calendar-check-o');
    }

    public function titleItem() {
        $this->setTitle(sprintf(_('Schedule item #%d'), $this->item->schedule_item_id));
        $this->setIcon('fa fa-calendar-check-o');
    }

    public function titleGroup() {
        $this->setTitle(sprintf(_('Schedule group #%d'), $this->group->schedule_group_id));
        $this->setIcon('fa fa-calendar-check-o');
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
     * @param int $id
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
        if ($this->item->getScheduleGroup()->getEvent()->event_id !== $this->getEvent()->event_id) {
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
        $groupControl->setGroup($this->getItem()->getScheduleGroup());
        /**
         * @var ItemControl $itemControl
         */
        $itemControl = $this->getComponent('itemControl');
        $itemControl->setItem($this->getItem());
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionGroups() {
        /**
         * @var AllPersonsGrid $component
         */
        $component = $this->getComponent('allPersonsGrid');
        $component->setEvent($this->getEvent());
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
     * @return AllPersonsGrid
     */
    public function createComponentAllPersonsGrid(): AllPersonsGrid {
        return $this->scheduleFactory->createAllPersonsGrid();
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

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    public function createComponentItemEditForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        return $control;
    }
}

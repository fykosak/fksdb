<?php

namespace FKSDB\Components\Factories;

use FKSDB\Components\Controls\Schedule\GroupControl;
use FKSDB\Components\Controls\Schedule\ItemControl;
use FKSDB\Components\Grids\Schedule\GroupsGrid;
use FKSDB\Components\Grids\Schedule\ItemsGrid;
use FKSDB\Components\Grids\Schedule\PersonsGrid;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Localization\ITranslator;

/**
 * Class ScheduleFactory
 * @package FKSDB\Components\Factories
 */
class ScheduleFactory {
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * ScheduleFactory constructor.
     * @param ITranslator $translator
     */
    public function __construct(ITranslator $translator) {
        $this->translator = $translator;
    }

    /**
     * @param ModelEvent $event
     * @return GroupsGrid
     */
    public function createGroupsGrid(ModelEvent $event): GroupsGrid {
        return new GroupsGrid($event);
    }

    /**
     * @return ItemsGrid
     */
    public function createItemsGrid(): ItemsGrid {
        return new ItemsGrid();
    }

    /**
     * @return PersonsGrid
     */
    public function createPersonsGrid(): PersonsGrid {
        return new PersonsGrid();
    }

    /**
     * @return GroupControl
     */
    public function createGroupControl(): GroupControl {
        return new GroupControl($this->translator);
    }

    /**
     * @return ItemControl
     */
    public function createItemControl(): ItemControl {
        return new ItemControl($this->translator);
    }

}

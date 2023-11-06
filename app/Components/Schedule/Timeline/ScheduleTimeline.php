<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Timeline;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Tracy\Debugger;

class ScheduleTimeline extends FrontEndComponent implements Chart
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, 'schedule.timeline');
        $this->event = $event;
    }

    public function getTitle(): Title
    {
        return new Title(null, '');
    }

    public function getDescription(): ?string
    {
        return '';
    }

    protected function getData(): array
    {
        $data = [];
        /** @var ScheduleGroupModel $group */
        foreach ($this->event->getScheduleGroups() as $group) {
            $datum = $group->__toArray();
            $datum['items'] = [];
            /** @var ScheduleItemModel $item */
            foreach ($group->getItems() as $item) {
                $datum['items'][] = $item->__toArray();
            }
            $data[] = $datum;
        }
        Debugger::barDump($data);
        return $data;
    }
}

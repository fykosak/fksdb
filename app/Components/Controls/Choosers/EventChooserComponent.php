<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

final class EventChooserComponent extends ChooserComponent
{

    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    protected function getItem(): NavItem
    {
        $items = [];
        $query = $this->event->event_type->getEvents()
            ->order('event_year DESC');
        /** @var EventModel $event */
        foreach ($query as $event) {
            $items[] = new NavItem(
                new Title(null, $event->name),
                'this',
                ['eventId' => $event->event_id],
                [],
                $event->event_id === $this->event->event_id
            );
        }
        return new NavItem(new Title(null, _('Event')), '#', [], $items);
    }
}

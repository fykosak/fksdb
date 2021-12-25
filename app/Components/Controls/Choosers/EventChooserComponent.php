<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\ServiceEvent;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

final class EventChooserComponent extends ChooserComponent
{

    private ModelEvent $event;
    private ServiceEvent $serviceEvent;

    public function __construct(Container $container, ModelEvent $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    final public function injectServiceEvent(ServiceEvent $serviceEvent): void
    {
        $this->serviceEvent = $serviceEvent;
    }

    protected function getItem(): NavItem
    {
        $items = [];
        $query = $this->serviceEvent->getTable()
            ->where('event_type_id=?', $this->event->event_type_id)
            ->order('event_year DESC');
        /** @var ModelEvent $event */
        foreach ($query as $event) {
            $items[] = new NavItem(
                new Title($event->name),
                'this',
                ['eventId' => $event->event_id],
                [],
                $event->event_id === $this->event->event_id
            );
        }
        return new NavItem(new Title(_('Event')), '#', [], $items);
    }
}

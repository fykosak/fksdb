<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventType;
use FKSDB\Models\ORM\Services\ServiceEvent;
use Fykosak\NetteORM\TypedTableSelection;
use FKSDB\Models\UI\Title;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;

class FyziklaniChooserComponent extends ChooserComponent {

    private ModelEvent $event;

    private ServiceEvent $serviceEvent;

    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container);
        $this->event = $event;
    }

    final public function injectServiceEvent(ServiceEvent $serviceEvent): void {
        $this->serviceEvent = $serviceEvent;
    }

    protected function getItems(): TypedTableSelection {
        return $this->serviceEvent->getTable()->where('event_type_id=?', ModelEventType::FYZIKLANI)->order('event_year DESC');
    }

    /**
     * @param ModelEvent $item
     * @return bool
     */
    public function isItemActive($item): bool {
        return $item->event_id === $this->event->event_id;
    }

    protected function getTitle(): Title {
        return new Title(_('Event'));
    }

    /**
     * @param ModelEvent $item
     * @return Title
     */
    public function getItemTitle($item): Title {
        return new Title($item->name);
    }

    /**
     * @param ModelEvent $item
     * @return string
     * @throws InvalidLinkException
     */
    public function getItemLink($item): string {
        return $this->getPresenter()->link('this', ['eventId' => $item->event_id]);
    }
}

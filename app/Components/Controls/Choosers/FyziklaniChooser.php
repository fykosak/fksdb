<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventType;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\ORM\Tables\TypedTableSelection;
use FKSDB\UI\Title;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FyziklaniChooser extends Chooser {
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * FyziklaniChooser constructor.
     * @param Container $container
     * @param ModelEvent $event
     */
    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container);
        $this->event = $event;
    }

    /**.
     * @param ServiceEvent $serviceEvent
     * @return void
     */
    public function injectServiceEvent(ServiceEvent $serviceEvent) {
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

    public function getTitle(): Title {
        return new Title(_('Event'));
    }

    /**
     * @param ModelEvent $item
     * @return string
     */
    public function getItemLabel($item): string {
        return $item->name;
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

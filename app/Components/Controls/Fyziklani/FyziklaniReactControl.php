<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\Models\ModelEvent;
use Nette\DI\Container;

/**
 * Class FyziklaniReactControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class FyziklaniReactControl extends ReactComponent {
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * FyziklaniReactControl constructor.
     * @param Container $container
     * @param ModelEvent $event
     */
    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container);
        $this->event = $event;
    }

    final protected function getEvent(): ModelEvent {
        return $this->event;
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

class TeamListComponent extends BaseComponent
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @throws NotSetGameParametersException
     */
    public function render(): void
    {
        $this->template->event = $this->event;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'list.latte');
    }
}

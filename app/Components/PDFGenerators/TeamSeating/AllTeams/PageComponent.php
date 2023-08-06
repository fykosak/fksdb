<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\AllTeams;

use FKSDB\Components\PDFGenerators\TeamSeating\SeatingPageComponent;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\Seating\RoomModel;
use Nette\DI\Container;

/**
 * @phpstan-extends SeatingPageComponent<null>
 */
class PageComponent extends SeatingPageComponent
{
    private RoomModel $room;
    protected EventModel $event;

    public function __construct(EventModel $event, RoomModel $room, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
        $this->room = $room;
    }

    /**
     * @param mixed $row
     * @phpstan-param ('dev'|'all')[] $params
     */
    final public function render($row, array $params = []): void
    {
        [$mode] = $params;
        $this->template->room = $this->room;
        $this->template->event = $this->event;
        switch ($mode) {
            case 'dev':
                $this->template->showTeamId = true;
                $this->template->showSeatId = true;
                $this->template->showTeamCategory = true;
                break;
            case 'all':
                $this->template->showTeamId = true;
                break;
        }
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . '../@layout.latte');
    }
}

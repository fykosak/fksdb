<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\AllTeams;

use FKSDB\Components\PDFGenerators\TeamSeating\SeatingPageComponent;
use FKSDB\Models\ORM\Models\Fyziklani\Seating\RoomModel;
use FKSDB\Models\ORM\Models\EventModel;
use Nette\DI\Container;

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
     */
    final public function render($row, array $params = []): void
    {
        [$mode] = $params;
        $this->getTemplate()->room = $this->room;
        $this->getTemplate()->event = $this->event;
        switch ($mode) {
            case 'dev':
                $this->getTemplate()->showTeamId = true;
                $this->getTemplate()->showSeatId = true;
                $this->getTemplate()->showTeamCategory = true;
                break;
            case 'all':
                $this->getTemplate()->showTeamId = true;
                break;
        }
        $this->getTemplate()->render(__DIR__ . DIRECTORY_SEPARATOR . '../@layout.latte');
    }
}

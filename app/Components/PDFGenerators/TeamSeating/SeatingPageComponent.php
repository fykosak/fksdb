<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating;

use FKSDB\Components\PDFGenerators\Providers\AbstractPageComponent;
use FKSDB\Models\ORM\Models\Fyziklani\Seating\RoomModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\DI\Container;

abstract class SeatingPageComponent extends AbstractPageComponent
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    final protected function innerRender(
        ?RoomModel $room,
        ModelEvent $event,
        ?string $sector = null,
        bool $showTeamId = false,
        bool $showBigNav = false,
        bool $showSeatId = false,
        bool $showTeamCategory = false
    ): void {
        $this->template->seatsParams = [
            'showTeamId' => $showTeamId,
            'showBigNav' => $showBigNav,
            'showSeatId' => $showSeatId,
            'showTeamCategory' => $showTeamCategory,
        ];
        $this->template->sector = $sector;
        $this->template->event = $event;
        $this->template->room = $room;
    }

    public function getPageFormat(): string
    {
        return self::FORMAT_B5_LANDSCAPE;
    }
}

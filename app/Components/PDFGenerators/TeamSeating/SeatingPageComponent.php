<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating;

use FKSDB\Components\PDFGenerators\Providers\AbstractPageComponent;
use FKSDB\Models\ORM\Models\Fyziklani\Seating\RoomModel;
use FKSDB\Models\ORM\Models\ModelEvent;

abstract class SeatingPageComponent extends AbstractPageComponent
{
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

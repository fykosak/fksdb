<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam;

use FKSDB\Components\PDFGenerators\TeamSeating\SeatingPageComponent;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;

class PageComponent extends SeatingPageComponent
{
    /**
     * @param TeamModel2 $row
     * @throws BadTypeException
     */
    final public function render($row, array $params = []): void
    {
        if (!$row instanceof TeamModel2) {
            throw new BadTypeException(TeamModel2::class, $row);
        }
        $this->template->rests = $row->getScheduleRest();
        $this->template->team = $row;
        $teamSeat = $row->getTeamSeat();
        $this->template->room = $teamSeat ? $teamSeat->fyziklani_seat->fyziklani_room : null;
        $this->template->event = $row->event;
        $this->template->sector = $teamSeat ? $teamSeat->fyziklani_seat->sector : null;
        $this->template->showBigNav = true;

        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.single.latte');
    }
}

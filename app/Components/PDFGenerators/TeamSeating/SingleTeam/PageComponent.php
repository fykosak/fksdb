<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam;

use FKSDB\Components\PDFGenerators\TeamSeating\SeatingPageComponent;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Database\Table\ActiveRow;

class PageComponent extends SeatingPageComponent
{

    /**
     * @param ModelFyziklaniTeam $row
     * @throws BadTypeException
     */
    final public function render($row, array $params = []): void
    {
        if (!$row instanceof ActiveRow) {
            throw new BadTypeException(ActiveRow::class, $row);
        }
        if (!$row instanceof ModelFyziklaniTeam) {
            $row = ModelFyziklaniTeam::createFromActiveRow($row);
        }
        $this->template->rests = $row->getScheduleRest();
        $this->template->team = $row;
        $teamSeat = $row->getTeamSeat();
        $this->innerRender($teamSeat ? $teamSeat->getSeat()->getRoom() : null);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.single.latte');
    }
}

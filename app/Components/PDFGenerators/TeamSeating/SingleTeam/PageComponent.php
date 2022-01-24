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
        $teamSeat = $row->getTeamSeat();
        if (!$teamSeat) {
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . '../Rooms/layout.na.latte');
            return;
        }
        $this->template->team = $row;

        $this->template->rests = $row->getScheduleRest();
        $this->innerRender($teamSeat->getSeat()->getRoom());
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.single.latte');
    }

    public function getPagesTemplatePath(): string
    {
        return $this->formatPathByFormat(self::FORMAT_B5_PORTRAIT);
    }
}

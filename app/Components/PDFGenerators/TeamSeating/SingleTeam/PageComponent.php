<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam;

use FKSDB\Components\PDFGenerators\Providers\AbstractPageComponent;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Database\Table\ActiveRow;

class PageComponent extends AbstractPageComponent
{

    /**
     * @param ModelFyziklaniTeam $row
     * @throws BadTypeException
     */
    final public function render($row): void
    {
        if (!$row instanceof ModelFyziklaniTeam) {
            throw new BadTypeException(ActiveRow::class, $row);
        }
        $teamSeat = $row->getTeamSeat();
        if (!$teamSeat) {
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . '../Rooms/layout.na.latte');
            return;
        }
        $this->template->team = $row;
        $this->template->rests = $row->getScheduleRest();
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . '../Rooms/layout.' . $teamSeat->getSeat()->getRoom()->layout . '.latte'
        );
    }

    public function getPagesTemplatePath(): string
    {
        return $this->formatPathByFormat(self::FORMAT_B5_PORTRAIT);
    }
}

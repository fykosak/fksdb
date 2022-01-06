<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam;

use FKSDB\Components\PDFGenerators\TeamSeating\SeatingPageComponent;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Database\Table\ActiveRow;

class PageComponent extends SeatingPageComponent
{

    /**
     * @param mixed $row
     * @throws BadTypeException
     */
    final public function render($row): void
    {
        parent::render($row);
        if (!$row instanceof ActiveRow) {
            throw new BadTypeException(ActiveRow::class, $row);
        }
        $team = ModelFyziklaniTeam::createFromActiveRow($row);
        $this->template->row = $team;
        $this->template->rests = $team->getScheduleRest();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.single.latte');
    }
}

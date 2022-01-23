<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating;

use FKSDB\Components\PDFGenerators\Providers\AbstractPageComponent;
use FKSDB\Models\ORM\Models\Fyziklani\Seating\RoomModel;
use FKSDB\Models\ORM\Services\Fyziklani\Seating\TeamSeatService;
use Nette\DI\Container;

abstract class SeatingPageComponent extends AbstractPageComponent
{

    /**
     * @param mixed $row
     */
    public function render($row): void
    {
        $this->template->room = $this->roomModel;
    }

    private function getRooms(): array
    {
        return [10, 11, 12, 13, 14];
    }

    public function getPagesTemplatePath(): string
    {
        return $this->formatPathByFormat(self::FORMAT_A5_PORTRAIT);
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating;

use FKSDB\Components\PDFGenerators\Providers\AbstractPageComponent;
use FKSDB\Models\ORM\Models\Fyziklani\Seating\RoomModel;

abstract class SeatingPageComponent extends AbstractPageComponent
{

    final protected function innerRender(RoomModel $room): void
    {
        $this->template->room = $room;
    }

    public function getPagesTemplatePath(): string
    {
        return $this->formatPathByFormat(self::FORMAT_A5_PORTRAIT);
    }
}

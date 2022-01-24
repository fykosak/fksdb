<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating;

use FKSDB\Components\PDFGenerators\Providers\AbstractPageComponent;
use FKSDB\Models\ORM\Models\Fyziklani\Seating\RoomModel;
use Nette\DI\Container;

abstract class SeatingPageComponent extends AbstractPageComponent
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    final protected function innerRender(RoomModel $room): void
    {
        $this->template->room = $room;
    }

    public function getPagesTemplatePath(): string
    {
        return $this->formatPathByFormat(self::FORMAT_A5_PORTRAIT);
    }
}

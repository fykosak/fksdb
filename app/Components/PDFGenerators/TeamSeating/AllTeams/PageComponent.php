<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\AllTeams;

use FKSDB\Components\PDFGenerators\Providers\AbstractPageComponent;
use FKSDB\Models\ORM\Models\Fyziklani\Seating\RoomModel;
use Nette\DI\Container;

class PageComponent extends AbstractPageComponent
{
    private RoomModel $roomModel;
   // private string $mode;

    public function __construct(RoomModel $roomModel, /*string $mode, */Container $container)
    {
        parent::__construct($container);
        // $this->mode = $mode;
        $this->roomModel = $roomModel;
    }

    /**
     * @param mixed $row
     */
    final public function render($row): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.' . $this->roomModel->layout . '.latte');
    }

    public function getPagesTemplatePath(): string
    {
        return $this->formatPathByFormat(self::FORMAT_B5_PORTRAIT);
    }
}

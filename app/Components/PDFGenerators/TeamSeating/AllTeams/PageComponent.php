<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\AllTeams;

use FKSDB\Components\PDFGenerators\TeamSeating\SeatingPageComponent;
use FKSDB\Models\ORM\Models\Fyziklani\Seating\RoomModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\DI\Container;
use Tracy\Debugger;

class PageComponent extends SeatingPageComponent
{
    private RoomModel $roomModel;
    private ModelEvent $event;

    public function __construct(ModelEvent $event, RoomModel $roomModel, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
        $this->roomModel = $roomModel;
    }

    /**
     * @param mixed $row
     */
    final public function render($row, array $params = []): void
    {
        [$mode] = $params;
        $this->innerRender($this->roomModel);
        $this->template->event = $this->event;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.' . $mode . '.latte');
    }

    public function getPagesTemplatePath(): string
    {
        return $this->formatPathByFormat(self::FORMAT_B5_PORTRAIT);
    }
}

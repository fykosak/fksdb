<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\AllTeams;

use FKSDB\Components\PDFGenerators\TeamSeating\SeatingPageComponent;
use FKSDB\Models\ORM\Models\Fyziklani\Seating\RoomModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\DI\Container;

class PageComponent extends SeatingPageComponent
{
    private RoomModel $roomModel;
    protected ModelEvent $event;

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
        switch ($mode) {
            case 'dev':
                $this->innerRender($this->roomModel, $this->event, null, true, false, true, true);
                break;
            case 'all':
                $this->innerRender($this->roomModel, $this->event, null, true);
                break;
            default:
                $this->innerRender($this->roomModel, $this->event);
        }
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . '../@layout.latte');
    }
}

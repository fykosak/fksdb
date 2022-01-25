<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Fyziklani;

use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\DI\Container;

class RoutingDownloadComponent extends BaseComponent
{

    private ModelEvent $event;

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;

    public function __construct(Container $container, ModelEvent $event)
    {
        parent::__construct($container);
        $this->event = $event;
        // $collector->registerJSFile('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.33/pdfmake.min.js');
        // $collector->registerJSFile('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.33/vfs_fonts.js');
    }

    final public function injectPrimary(ServiceFyziklaniTeam $serviceFyziklaniTeam): void
    {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    final public function render(): void
    {
        $rooms = [];// $this->serviceFyziklaniRoom->getRoomsByIds($this->event->getParameter(null, 'rooms'));
        $this->template->rooms = $rooms;
        // $this->template->buildings = $this->event->getParameter('gameSetup')['buildings'];
        $this->template->teams = $this->serviceFyziklaniTeam->serialiseTeams($this->event);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'RoutingDownload.latte');
    }
}

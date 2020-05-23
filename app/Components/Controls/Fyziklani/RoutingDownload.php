<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Components\Controls\BaseComponent;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\DI\Container;
use Nette\Templating\FileTemplate;

/**
 * Class Routing
 * @property FileTemplate $template
 *
 */
class RoutingDownload extends BaseComponent {
    /**
     * @var bool
     */
    private static $JSAttached = false;
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var ServiceFyziklaniRoom
     */
    private $serviceFyziklaniRoom;

    /**
     * RoutingDownload constructor.
     * @param Container $container
     * @param ModelEvent $event
     */
    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param ServiceFyziklaniRoom $serviceFyziklaniRoom
     * @return void
     */
    public function injectPrimary(ServiceFyziklaniTeam $serviceFyziklaniTeam, ServiceFyziklaniRoom $serviceFyziklaniRoom) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceFyziklaniRoom = $serviceFyziklaniRoom;
    }

    /**
     * @return void
     */
    public function render() {
        $rooms = [];// $this->serviceFyziklaniRoom->getRoomsByIds($this->event->getParameter(null, 'rooms'));

        $this->template->rooms = $rooms;
        // $this->template->buildings = $this->event->getParameter('gameSetup')['buildings'];
        $this->template->teams = $this->serviceFyziklaniTeam->getTeamsAsArray($this->event);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'RoutingDownload.latte');
        $this->template->render();
    }

    /**
     * @param $obj
     * @return void
     */
    protected function attached($obj) {
        parent::attached($obj);
        if (!self::$JSAttached && $obj instanceof IJavaScriptCollector) {
            self::$JSAttached = true;
            $obj->registerJSFile('js/routingPdf.js');
            $obj->registerJSFile('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.33/pdfmake.min.js');
            $obj->registerJSFile('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.33/vfs_fonts.js');
        }
    }
}

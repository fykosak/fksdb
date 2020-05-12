<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Application\IJavaScriptCollector;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\UI\Control;
use Nette\DI\Container;
use Nette\Localization\ITranslator;

/**
 * Class Routing
 *
 *
 */
class RoutingDownload extends Control {
    /**
     * @var bool
     */
    private static $JSAttached = false;
    /**
     * @var ITranslator
     */
    private $translator;
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
        $this->translator = $container->getByType(ITranslator::class);
        $this->event = $event;
        $this->serviceFyziklaniTeam = $container->getByType(ServiceFyziklaniTeam::class);
        $this->serviceFyziklaniRoom = $container->getByType(ServiceFyziklaniRoom::class);

        parent::__construct();
    }

    /**
     *
     */
    public function render() {
        $rooms = $this->serviceFyziklaniRoom->getRoomsByIds($this->event->getParameter('rooms'));

        $this->template->rooms = $rooms;
        // $this->template->buildings = $this->event->getParameter('gameSetup')['buildings'];
        $this->template->teams = $this->serviceFyziklaniTeam->getTeamsAsArray($this->event);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'RoutingDownload.latte');
        $this->template->setTranslator($this->translator);
        $this->template->render();
    }

    /**
     * @param $obj
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

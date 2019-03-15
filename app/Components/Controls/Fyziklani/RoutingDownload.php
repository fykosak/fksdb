<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Application\IJavaScriptCollector;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;


/**
 * Class Routing
 * @property FileTemplate $template
 *
 */
class RoutingDownload extends Control {
    /**
     * @var bool
     */
    private static $JSAttached = false;
    /**
     * @var
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
     * @var \ServiceFyziklaniRoom
     */
    private $serviceFyziklaniRoom;

    /**
     * RoutingDownload constructor.
     * @param ModelEvent $event
     * @param ITranslator $translator
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param \ServiceFyziklaniRoom $serviceFyziklaniRoom
     */
    public function __construct(ModelEvent $event, ITranslator $translator, ServiceFyziklaniTeam $serviceFyziklaniTeam, \ServiceFyziklaniRoom $serviceFyziklaniRoom) {
        $this->translator = $translator;
        $this->event = $event;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceFyziklaniRoom = $serviceFyziklaniRoom;

        parent::__construct();
    }

    /**
     *
     */
    public function render() {
        $rooms = $this->serviceFyziklaniRoom->getRoomsByIds($this->event->getParameter('gameSetup')['rooms']);

        $this->template->rooms = $rooms;
        $this->template->buildings = $this->event->getParameter('gameSetup')['buildings'];
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

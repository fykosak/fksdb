<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Application\IJavaScriptCollector;
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
     * @var array
     */
    private $buildings;

    /**
     * @var array
     */
    private $rooms;
    /**
     * @var array
     */
    private $teams;

    /**
     * @var bool
     */
    private static $JSAttached = false;
    /**
     * @var
     */
    private $translator;

    public function __construct(ITranslator $translator) {
        $this->translator = $translator;
        parent::__construct();
    }

    public function setRooms($rooms) {
        $this->rooms = $rooms;
    }

    public function setBuildings($buildings) {
        $this->buildings = $buildings;
    }

    public function setTeams($teams) {
        $this->teams = $teams;
    }

    public function render() {
        $this->template->rooms = $this->rooms;
        $this->template->buildings = $this->buildings;
        $this->template->teams = $this->teams;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'RoutingDownload.latte');
        $this->template->setTranslator($this->translator);
        $this->template->render();
    }

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

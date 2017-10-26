<?php
/**
 * @author Michal Červeňák <miso@fykos.cz>
 * trait content contest, year, and series chooser
 */

namespace FKSDB\Components\Controls\Navs;

use FKSDB\Components\Controls\Choosers\BrawlChooser;
use FKSDB\Components\Controls\Choosers\LanguageChooser;
use Nette\Application\UI\Control;
use Nette\Http\Session;

class BrawlNav extends Control {
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var \ServiceEvent
     */
    protected $serviceEvent;


    public function __construct(\ServiceEvent $serviceEvent, $session) {
        parent::__construct();
        $this->serviceEvent = $serviceEvent;
        $this->session = $session;
    }

    /**
     * @return BrawlChooser
     */
    protected function createComponentBrawlChooser() {
        $control = new BrawlChooser($this->serviceEvent);
        return $control;
    }

    /**
     * @return LanguageChooser
     */
    protected function createComponentLanguageChooser() {
        $control = new LanguageChooser($this->session);
        return $control;
    }

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR.'BrawlNav.latte');
        $this->template->render();
    }

    /**
     * @param $params object
     * @return object
     * redirect to correct URL
     */
    public function init($params) {
        $redirect = false;
        /**
         * @var $languageChooser LanguageChooser
         */
        $languageChooser = $this['languageChooser'];
        $redirect = $redirect || $languageChooser->syncRedirect($params);
        /**
         * @var $brawlChooser BrawlChooser
         */
        $brawlChooser = $this['brawlChooser'];
        $redirect = $redirect || $brawlChooser->syncRedirect($params);
        if ($redirect) {
            return $params;
        } else {
            return null;
        }
    }
}

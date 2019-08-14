<?php
/**
 * @author Michal Červeňák <miso@fykos.cz>
 * trait content contest, year, and series chooser
 */

namespace FKSDB\Components\Controls\Navs;

use Exception;
use FKSDB\Components\Controls\Choosers\LanguageChooser;
use Nette\Application\UI\Control;
use Nette\Http\Session;

/**
 * Class LanguageNav
 * @package FKSDB\Components\Controls\Navs
 */
class LanguageNav extends Control {
    /**
     * @var Session
     */
    private $session;

    /**
     * LanguageNav constructor.
     * @param Session $session
     */
    public function __construct(Session $session) {
        parent::__construct();
        $this->session = $session;
    }

    /**
     * @return LanguageChooser
     */
    protected function createComponentLanguageChooser() {
        $control = new LanguageChooser($this->session);
        return $control;
    }

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'LanguageNav.latte');
        $this->template->render();
    }

    /**
     * @param object $params
     * @return object
     * redirect to correct URL
     * @throws Exception
     */
    public function init($params) {
        /**
         * @var LanguageChooser $languageChooser
         */
        $languageChooser =  $this->getComponent('languageChooser');
        $redirect = $languageChooser->syncRedirect($params);
        if ($redirect) {
            return $params;
        } else {
            return null;
        }
    }
}

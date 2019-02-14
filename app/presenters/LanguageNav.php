<?php
/**
 * @author Michal Červeňák <miso@fykos.cz>
 * trait content contest, year, series and lang chooser
 */

use \FKSDB\Components\Controls;

/**
 * Trait ContestNav
 * @param ServiceContest $serviceContest
 */
trait LanguageNav {

    /**
     * @persistent
     */
    //protected $lang;

    /**
     * @var boolean
     */
    private $initialized = false;
    /**
     * @var object
     * @property integer contestId
     * @property integer year
     * @property integer series
     */
    private $newParams = null;

    /**
     * @return Controls\Navs\LanguageNav
     */
    protected function createComponentLanguageNav() {
        $control = new Controls\Navs\LanguageNav($this->session);
        return $control;
    }

    /**
     * @return mixed
     */
    public function getSelectedLanguage() {
        $this->init();
        return $this->lang;
    }

    /**
     * rewrite coreBasePresenter getLang
     * @return string
     */
    public function getLang() {
        return $this->getSelectedLanguage() ?: parent::getLang();
    }

    public function init() {
        if ($this->initialized) {
            return;
        }
        /**
         * @var Controls\Navs\LanguageNav $languageNav
         */
        $languageNav =  $this->getComponent('languageNav');
        $this->newParams = $languageNav->init((object)[
            'lang' => $this->lang,
        ]);
    }

    /**
     * redirect to correct URL
     */
    protected function startupRedirects() {
        $this->init();
         if (is_null($this->newParams)) {
            return;
        }
        $this->redirect('this', [
             'lang' => $this->newParams->lang ?: $this->lang,
        ]);
    }
}

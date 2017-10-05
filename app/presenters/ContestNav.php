<?php
/**
 * @author Michal Červeňák <miso@fykos.cz>
 * trait content contest, year, and series chooser
 */

use \FKSDB\Components\Controls;

trait ContestNav {

    /**
     * @var integer
     * @persistent
     */
    public $contestId;

    /**
     * @var integer
     * @persistent
     */
    public $year;

    /**
     * @var mixed
     * @persistent
     */
    public $lang;

    /**
     * @var integer
     * @persistent
     */
    public $series;

    private $initialized = false;
    /**
     * @var object
     * @property integer contestId
     * @property integer year
     * @property integer series
     */
    private $newParams = null;

    protected function createComponentContestNav() {
        $control = new Controls\ContestNav\ContestNav($this->getYearCalculator(), $this->seriesCalculator, $this->session, $this->serviceContest, $this->getTranslator());
        $control->setRole($this->role);
        return $control;
    }

    /**
     * @return ModelContest
     */
    public function getSelectedContest() {
        $this->init();
        /**
         * @var $contestNav Controls\ContestNav\ContestNav
         */
        $contestNav = $this['contestNav'];
        return $contestNav->getSelectedContest();
    }

    /**
     * @return int
     */
    public function getSelectedYear() {
        $this->init();
        /**
         * @var $contestNav Controls\ContestNav\ContestNav
         */
        $contestNav = $this['contestNav'];
        return $contestNav->getSelectedYear();
    }

    /**
     * @return int
     */
    public function getSelectedSeries() {
        $this->init();
        /**
         * @var $contestNav Controls\ContestNav\ContestNav
         */
        $contestNav = $this['contestNav'];
        return $contestNav->getSelectedSeries();
    }

    public function getSelectedLanguage() {
        $this->init();
        /**
         * @var $contestNav ContestNav
         */
        $contestNav = $this['contestNav'];
        return $contestNav->getSelectedLanguage();
    }

    public function init() {
        if ($this->initialized) {
            return;
        }
        /**
         * @var $contestNav Controls\ContestNav\ContestNav
         */
        $contestNav = $this['contestNav'];
        $this->newParams = $contestNav->init((object)[
            'year' => $this->year,
            'contestId' => $this->contestId,
            'series' => $this->series,
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
            'year' => $this->newParams->year ?: $this->year,
            'contestId' => $this->newParams->contestId ?: $this->contestId,
            'series' => $this->newParams->series ?: $this->series,
        ]);
    }

}
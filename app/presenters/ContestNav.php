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

    protected function createComponentContestNav() {
        $control = new Controls\ContestNav\ContestNav($this->getYearCalculator(), $this->seriesCalculator, $this->session, $this->serviceContest, $this->getTranslator());
        $control->setRole($this->role);
        return $control;
    }

    /**
     * @return ModelContest
     */
    public function getSelectedContest() {
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
        /**
         * @var $contestNav Controls\ContestNav\ContestNav
         */
        $contestNav = $this['contestNav'];
        return $contestNav->getSelectedSeries();
    }

    public function getSelectedLanguage() {
        /**
         * @var $contestNav ContestNav
         */
        $contestNav = $this['contestNav'];
        return $contestNav->getSelectedLanguage();
    }

    /**
     * redirect to correct URL
     */
    protected function startupRedirects() {
        /**
         * @var $contestNav Controls\ContestNav\ContestNav
         */
        $contestNav = $this['contestNav'];
        $params = $contestNav->getSyncRedirectParams((object)[
            'year' => $this->year,
            'contestId' => $this->contestId,
            'series' => $this->series,
        ]);
        if (is_null($params)) {
            return;
        }

        $this->redirect('this', [
            'year' => $params->year ?: $this->year,
            'contestId' => $params->contestId ?: $this->contestId,
            'series' => $params->series ?: $this->series,
        ]);
    }

}
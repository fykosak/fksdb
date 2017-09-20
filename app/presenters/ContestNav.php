<?php
/**
 * @author Michal Červeňák <miso@fykos.cz>
 * trait content contest, year, and series chooser
 */

use FKSDB\Components\Controls\ContestNav\ContestChooser;
use FKSDB\Components\Controls\ContestNav\YearChooser;
use Nette\Application\BadRequestException;

trait ContestNav {


    protected function createComponentContestNav() {
        $control = new \FKSDB\Components\Controls\ContestNav\ContestNav($this->getYearCalculator(), $this->seriesCalculator, $this->session, $this->serviceContest, $this->getTranslator());
        $control->setRole($this->role);
        return $control;
    }

    /**
     * @return ModelContest
     */
    public function getSelectedContest() {
        /**
         * @var $contestNav \FKSDB\Components\Controls\ContestNav\ContestNav
         */
        $contestNav = $this['contestNav'];
        return $contestNav->getSelectedContest();
    }

    /**
     * @return int
     */
    public function getSelectedYear() {
        /**
         * @var $contestNav \FKSDB\Components\Controls\ContestNav\ContestNav
         */
        $contestNav = $this['contestNav'];
        return $contestNav->getSelectedYear();
    }

    /**
     * @return int
     */
    public function getSelectedSeries() {
        /**
         * @var $contestNav \FKSDB\Components\Controls\ContestNav\ContestNav
         */
        $contestNav = $this['contestNav'];
        return -1;// $contestNav->getSelectedSeries();
    }

    /**
     * redirect to correct URL
     */
    protected function startupRedirects() {
        /**
         * @var $contestNav \FKSDB\Components\Controls\ContestNav\ContestNav
         */
        $contestNav = $this['contestNav'];
        $params = $contestNav->getSyncRedirectParams((object)[
            'year' => $this->year,
            'contestId' => $this->contestId,
            'series' => $this->series,
        ]);
        if (is_null($params->year) && is_null($params->contestId) && is_null($params->series)) {
            return;
        }
        $this->redirect('this', [
            'year' => $params->year ?: $this->year,
            'contestId' => $params->contestId ?: $this->contestId,
            'series' => $params->series ?: $this->series,
        ]);
    }

}
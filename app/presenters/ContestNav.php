<?php
/**
 * @author Michal Červeňák <miso@fykos.cz>
 * trait content contest, year, and series chooser
 */

use FKSDB\Components\Controls\Nav\ContestChooser;
use FKSDB\Components\Controls\Nav\YearChooser;
use Nette\Application\BadRequestException;

trait ContestNav {


    protected function createComponentContestChooser($name) {
        $control = new ContestChooser($this->session, $this->yearCalculator, $this->serviceContest);
        $control->setRole($this->role);
        return $control;
    }

    protected function createComponentYearChooser($name) {
        $control = new YearChooser($this->session, $this->getYearCalculator(), $this->getServiceContest());
        $this->getSelectedContest();
        $control->setRole($this->role);
        // $control->setContest();
        return $control;
    }

    /**
     * @return ModelContest
     */
    public function getSelectedContest() {
        /**
         * @var $contestChooser ContestChooser
         */
        $contestChooser = $this['contestChooser'];
        if (!$contestChooser->isValid()) {
            \Nette\Diagnostics\Debugger::barDump($contestChooser);
            // $this->redirect(':Public:Chooser:default');
        }
        return $contestChooser->getContest();
    }

    /**
     * @return int
     */
    public function getSelectedYear() {
        /**
         * @var $yearChooser YearChooser
         */
        $yearChooser = $this['yearChooser'];
        if (!$yearChooser->isValid()) {
            \Nette\Diagnostics\Debugger::barDump($yearChooser);
            //$this->redirect(':Public:Chooser:default');
        }
        return $yearChooser->getYear();
    }

    /**
     * redirect to correct URL
     */
    protected function startupRedirects() {
        /**
         * @var $contestChooser ContestChooser
         */
        $contestChooser = $this['contestChooser'];
        $contestId = $contestChooser->syncRedirect();
        /**
         * @var $yearChooser YearChooser
         */
        $yearChooser = $this['yearChooser'];
        $year = $yearChooser->syncRedirect();
        \Nette\Diagnostics\Debugger::barDump($year . 'A');
        if (is_null($year) && is_null($contestId)) {
        } else {
            \Nette\Diagnostics\Debugger::barDump([
                'year' => $year ?: $year - $year,
                'contestId' => $contestId ?: $this->contestId,
            ]);

            $this->redirect('this', [
                'year' => $year ?: $year - $year,
                'contestId' => $contestId ?: $this->contestId,
            ]);
        }

    }

}
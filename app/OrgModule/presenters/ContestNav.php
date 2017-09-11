<?php
/**
 * @author Michal Červeňák <miso@fykos.cz>
 * trait content contest, year, and series chooser
 */

namespace FKSDB\OrgModule\presenters;

use FKSDB\Components\Controls\Nav\ContestChooser;
use FKSDB\Components\Controls\Nav\YearChooser;


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

}
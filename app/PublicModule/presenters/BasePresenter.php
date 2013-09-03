<?php

namespace PublicModule;

use AuthenticatedPresenter;
use IContestPresenter;
use ModelContest;

/**
 * Current year of FYKOS.
 * 
 * @todo Contest should be from URL and year should be current.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class BasePresenter extends AuthenticatedPresenter implements IContestPresenter {

    /** @var ModelContest */
    private $selectedContest;

    public function getSelectedContest() {
        if ($this->selectedContest === null) {
            $this->selectedContest = $this->serviceContest->findByPrimary(ModelContest::ID_FYKOS);
        }
        return $this->selectedContest;
    }

    public function getSelectedYear() {
        return $this->yearCalculator->getCurrentYear($this->getSelectedContest()->contest_id);
    }

}

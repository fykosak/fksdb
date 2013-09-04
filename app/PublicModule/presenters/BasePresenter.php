<?php

namespace PublicModule;

use AuthenticatedPresenter;
use IContestPresenter;
use ModelContest;
use ModelContestant;

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

    /** @var ModelContestant|null|false */
    private $contestant = false;

    public function getSelectedContest() {
        if ($this->selectedContest === null) {
            $this->selectedContest = $this->serviceContest->findByPrimary(ModelContest::ID_FYKOS);
        }
        return $this->selectedContest;
    }

    public function getSelectedYear() {
        return $this->yearCalculator->getCurrentYear($this->getSelectedContest()->contest_id);
    }

    public function getContestant() {
        if ($this->contestant === false) {
            $person = $this->user->getIdentity()->getPerson();
            $contestant = $person->getContestants()
                    ->where(array(
                'contest_id' => $this->getSelectedContest()->contest_id,
                'year' => $this->getSelectedYear()
            ))->fetch();

            $this->contestant = $contestant ? ModelContestant::createFromTableRow($contestant) : null;
        }

        return $this->contestant;
    }

}

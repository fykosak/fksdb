<?php

namespace PublicModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\ContestChooser;
use IContestPresenter;
use ModelContestant;
use ModelRole;
use Nette\Application\BadRequestException;

/**
 * Current year of FYKOS.
 * 
 * @todo Contest should be from URL and year should be current.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class BasePresenter extends AuthenticatedPresenter implements IContestPresenter {

    const PRESETS_KEY = 'publicPresets';

    /**
     * @persistent
     */
    public $contestId;

    protected function createComponentContestChooser($name) {
        $control = new ContestChooser($this->session, $this->yearCalculator, $this->serviceContest);
        $control->setContests(ModelRole::CONTESTANT);
        return $control;
    }

    /** @var ModelContestant|null|false */
    private $contestant = false;

    public function getSelectedContest() {
        $contestChooser = $this['contestChooser'];
        if (!$contestChooser->isValid()) {
            throw new BadRequestException('No contests available.', 403);
        }
        return $contestChooser->getContest();
    }

    public function getSelectedYear() {
        $contestChooser = $this['contestChooser'];
        if (!$contestChooser->isValid()) {
            throw new BadRequestException('No contests available.', 403);
        }
        return $contestChooser->getYear();
    }

    public function getSelectedAcademicYear() {
        return $this->yearCalculator->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    public function getContestant() {
        if ($this->contestant === false) {
            $person = $this->user->getIdentity()->getPerson();
            $contestants = $person->getActiveContestants($this->yearCalculator);
            $this->contestant = $contestants[$this->getSelectedContest()->contest_id];
        }

        return $this->contestant;
    }

}

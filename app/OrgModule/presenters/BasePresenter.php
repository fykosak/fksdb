<?php

namespace OrgModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\ContestChooser;
use IContestPresenter;
use ModelRole;

/**
 * Presenter keeps chosen contest and year in session.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends AuthenticatedPresenter implements IContestPresenter {

    const PRESETS_KEY = 'orgPresets';

    /**
     * @var int
     * @persistent
     */
    public $contestId;

    /**
     * @var int
     * @persistent
     */
    public $year;

    protected function startup() {
        parent::startup();

        if (!$this['contestChooser']->isValid()) {
            $this->redirect(':Authentication:login');
        }
    }

    protected function createComponentContestChooser($name) {
        $control = new ContestChooser(ModelRole::ORG, $this->session, $this->yearCalculator, $this->serviceContest);
        return $control;
    }

    public function getSelectedContest() {
        return $this['contestChooser']->getContest();
    }

    public function getSelectedYear() {
        return $this['contestChooser']->getYear();
    }

}

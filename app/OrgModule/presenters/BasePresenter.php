<?php

namespace OrgModule;

use AuthenticatedPresenter;
use IContestPresenter;
use ModelRole;

/**
 * Presenter keeps chosen contest, year and language in session.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends AuthenticatedPresenter implements IContestPresenter {

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

    /**
     * @var int
     * @persistent
     */
    public $lang;

    /**
     * @var string
     */
    protected $role = ModelRole::ORG;

    protected function startup() {
        parent::startup();
    }


    public function getSelectedContest() {
        return $this->serviceContest->findByPrimary($this->contestId);
    }

    public function getSelectedYear() {
        return $this->year;
    }

    public function getSelectedAcademicYear() {
        return $this->yearCalculator->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    public function getSelectedLanguage() {
        $this->lang;
    }

    protected function getChoosers() {
        return ['lang', 'dispatch', 'year'];
    }
}

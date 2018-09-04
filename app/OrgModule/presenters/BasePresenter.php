<?php

namespace OrgModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Components\Controls\LanguageChooser;
use IContestPresenter;
use ModelRole;
use Nette\Application\BadRequestException;

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

    protected function startup() {
        parent::startup();
        $this['contestChooser']->syncRedirect();
        $this['languageChooser']->syncRedirect();
    }

    protected function createComponentContestChooser($name) {
        $control = new ContestChooser($this->session, $this->yearCalculator, $this->serviceContest);
        $control->setContests(ModelRole::ORG);
        return $control;
    }

    protected function createComponentLanguageChooser($name) {
        $control = new LanguageChooser($this->session);
        return $control;
    }

    /**
     * @return \ModelContest
     * @throws BadRequestException
     */
    public function getSelectedContest() {
        $contestChooser = $this['contestChooser'];
        if (!$contestChooser->isValid()) {
            throw new BadRequestException('No contests available.', 403);
        }
        return $contestChooser->getContest();
    }

    /**
     * @return int
     * @throws BadRequestException
     */
    public function getSelectedYear() {
        $contestChooser = $this['contestChooser'];
        if (!$contestChooser->isValid()) {
            throw new BadRequestException('No contests available.', 403);
        }
        return $contestChooser->getYear();
    }

    /**
     * @return int|mixed
     * @throws BadRequestException
     */
    public function getSelectedAcademicYear() {
        return $this->yearCalculator->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    /**
     * @return mixed
     * @throws BadRequestException
     */
    public function getSelectedLanguage() {
        $languageChooser = $this['languageChooser'];
        if (!$languageChooser->isValid()) {
            throw new BadRequestException('No languages available.', 403);
        }
        return $languageChooser->getLanguage();
    }

    protected function getNavBarVariant() {
        /**
         * @var $contest \ModelContest
         */
        $contest = $this->serviceContest->findByPrimary($this->contestId);
        if ($contest) {
            return [$contest->getContestSymbol(), 'dark'];
        }
        return [null, null];
    }

    public function getSubtitle() {
        return sprintf(_('%d. ročník'), $this->year);
    }
    public function getNavRoot() {
        return 'org.dashboard.default';
    }
}

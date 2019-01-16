<?php

namespace OrgModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Components\Controls\LanguageChooser;
use FKSDB\ORM\ModelRole;
use IContestPresenter;
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
        /**
         * @var ContestChooser $contestChooser
         * @var LanguageChooser $languageChooser
         */
        $contestChooser =  $this->getComponent('contestChooser');
        $contestChooser->syncRedirect();
        $languageChooser =  $this->getComponent('languageChooser');
        $languageChooser->syncRedirect();
    }

    protected function createComponentContestChooser(): ContestChooser {
        $control = new ContestChooser($this->session, $this->yearCalculator, $this->serviceContest);
        $control->setContests(ModelRole::ORG);
        return $control;
    }

    protected function createComponentLanguageChooser(): LanguageChooser {
        return new LanguageChooser($this->session);
    }

    /**
     * @return \FKSDB\ORM\ModelContest
     * @throws BadRequestException
     */
    public function getSelectedContest() {
        /**
         * @var ContestChooser $contestChooser
         */
        $contestChooser =  $this->getComponent('contestChooser');
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
        /**
         * @var ContestChooser $contestChooser
         */
        $contestChooser =  $this->getComponent('contestChooser');
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
        /**
         * @var LanguageChooser $languageChooser
         */
        $languageChooser =  $this->getComponent('languageChooser');
        if (!$languageChooser->isValid()) {
            throw new BadRequestException('No languages available.', 403);
        }
        return $languageChooser->getLanguage();
    }

    protected function getNavBarVariant(): array {
        /**
         * @var $contest \FKSDB\ORM\ModelContest
         */
        $contest = $this->serviceContest->findByPrimary($this->contestId);
        if ($contest) {
            return [$contest->getContestSymbol(), 'navbar-dark bg-' . $contest->getContestSymbol()];
        }
        return [null, null];
    }

    public function getSubtitle(): string {
        return sprintf(_('%d. ročník'), $this->year);
    }

    public function getNavRoots(): array {
        return ['org.dashboard.default'];
    }
}

<?php

use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Components\Controls\LanguageChooser;
use FKSDB\ORM\ModelContest;
use Nette\Application\BadRequestException;

abstract class ContestPresenter extends AuthenticatedPresenter implements IContestPresenter {

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
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\ForbiddenRequestException
     */
    protected function startup() {
        parent::startup();
        /**
         * @var ContestChooser $contestChooser
         * @var LanguageChooser $languageChooser
         */
        $contestChooser = $this->getComponent('contestChooser');
        $contestChooser->syncRedirect();
        $languageChooser = $this->getComponent('languageChooser');
        $languageChooser->syncRedirect();
    }

    /**
     * @return ContestChooser
     */
    abstract protected function createComponentContestChooser(): ContestChooser;

    /**
     * @return LanguageChooser
     */
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
        $contestChooser = $this->getComponent('contestChooser');
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
        $contestChooser = $this->getComponent('contestChooser');
        if (!$contestChooser->isValid()) {
            throw new BadRequestException('No contests available.', 403);
        }
        return $contestChooser->getYear();
    }

    /**
     * @return int
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
        $languageChooser = $this->getComponent('languageChooser');
        if (!$languageChooser->isValid()) {
            throw new BadRequestException('No languages available.', 403);
        }
        return $languageChooser->getLanguage();
    }

    /**
     * @return array
     */
    protected function getNavBarVariant(): array {
        $row = $this->serviceContest->findByPrimary($this->contestId);
        if ($row) {
            $contest = ModelContest::createFromTableRow($row);
            return [$contest->getContestSymbol(), 'navbar-dark bg-' . $contest->getContestSymbol()];
        }
        return parent::getNavBarVariant();
    }

    /**
     * @return string
     */
    public function getSubTitle(): string {
        return sprintf(_('%d. ročník'), $this->year);
    }
}

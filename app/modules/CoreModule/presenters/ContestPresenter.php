<?php

use FKSDB\Components\Controls\ContestChooser;
use FKSDB\CoreModule\IContestPresenter;
use FKSDB\ORM\Models\ModelContest;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

/**
 * Class ContestPresenter
 */
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
     * @throws AbortException
     * @throws ForbiddenRequestException
     */
    protected function startup() {
        parent::startup();
        /**
         * @var ContestChooser $contestChooser
         */
        $contestChooser = $this->getComponent('contestChooser');
        $contestChooser->syncRedirect();
    }

    /**
     * @return ContestChooser
     */
    abstract protected function createComponentContestChooser(): ContestChooser;

    /**
     * @return ModelContest
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
    public function getSelectedYear(): int {
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
    public function getSelectedAcademicYear(): int {
        return $this->yearCalculator->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    /**
     * @return array
     */
    protected function getNavBarVariant(): array {
        $row = $this->getServiceContest()->findByPrimary($this->contestId);
        if ($row) {
            $contest = ModelContest::createFromActiveRow($row);
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

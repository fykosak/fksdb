<?php

use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Exceptions\BadTypeException;
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
        $contestChooser = $this->getComponent('contestChooser');
        if (!$contestChooser instanceof ContestChooser) {
            throw new BadTypeException(ContestChooser::class, $contestChooser);
        }
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
        $contestChooser = $this->getComponent('contestChooser');
        if (!$contestChooser instanceof ContestChooser) {
            throw new BadTypeException(ContestChooser::class, $contestChooser);
        }
        if (!$contestChooser->isValid()) {
            throw new ForbiddenRequestException('No contests available.');
        }
        return $contestChooser->getContest();
    }

    /**
     * @return int
     * @throws BadRequestException
     */
    public function getSelectedYear() {
        $contestChooser = $this->getComponent('contestChooser');
        if (!$contestChooser instanceof ContestChooser) {
            throw new BadTypeException(ContestChooser::class, $contestChooser);
        }
        if (!$contestChooser->isValid()) {
            throw new ForbiddenRequestException('No contests available.');
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

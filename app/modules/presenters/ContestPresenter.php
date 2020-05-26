<?php

use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\UI\PageStyleContainer;
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
     * @internal
     */
    public $contestId;

    /**
     * @var int
     * @persistent
     * @internal
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

    abstract protected function createComponentContestChooser(): ContestChooser;

    /**
     * @return ModelContest
     * @throws BadRequestException
     */
    public function getSelectedContest(): ModelContest {
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
    public function getSelectedYear(): int {
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
    public function getSelectedAcademicYear(): int {
        return $this->getYearCalculator()->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    protected function getPageStyleContainer(): PageStyleContainer {
        $container = parent::getPageStyleContainer();
        /** @var ModelContest $contest */
        $contest = $this->getServiceContest()->findByPrimary($this->contestId);
        if ($contest) {
            $container->styleId = $contest->getContestSymbol();
            $container->navBarClassName = 'navbar-dark bg-' . $contest->getContestSymbol();
        }
        return $container;
    }

    /**
     * @param string $title
     * @param string $icon
     * @param string $subTitle
     * @return void
     */
    protected function setTitle(string $title, string $icon = '', string $subTitle = '') {
        parent::setTitle($title, $icon, sprintf(_('%d. ročník'), $this->year) . ' ' . $subTitle);
    }
}

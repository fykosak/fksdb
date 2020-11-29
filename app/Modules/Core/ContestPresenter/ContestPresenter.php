<?php

namespace FKSDB\Modules\Core\ContestPresenter;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Components\Controls\Choosers\ContestChooser;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\UI\PageTitle;
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
     * @throws AbortException
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    protected function startup(): void {
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
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    public function getSelectedContest(): ?ModelContest {
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
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    public function getSelectedYear(): ?int {
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
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    public function getSelectedAcademicYear(): int {
        return $this->yearCalculator->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    protected function beforeRender(): void {
        try {
            $contest = $this->getSelectedContest();
        } catch (BadRequestException $exception) {

        }
        if (isset($contest) && $contest) {
            $this->getPageStyleContainer()->styleId = $contest->getContestSymbol();
            $this->getPageStyleContainer()->setNavBarClassName('navbar-dark bg-' . $contest->getContestSymbol());
        }
        parent::beforeRender();
    }

    protected function setPageTitle(PageTitle $pageTitle): void {
        $pageTitle->subTitle = sprintf(_('%d. year'), $this->year) . ' ' . $pageTitle->subTitle;
        parent::setPageTitle($pageTitle);
    }
}

<?php

namespace FKSDB\Modules\Core\ContestPresenter;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Components\Controls\ContestChooser;
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

    protected function beforeRender() {
        try {
            $contest = $this->getSelectedContest();
        } catch (BadRequestException $exception) {

        }
        if (isset($contest) && $contest) {
            $this->getPageStyleContainer()->styleId = $contest->getContestSymbol();
            $this->getPageStyleContainer()->navBarClassName = 'navbar-dark bg-' . $contest->getContestSymbol();
        }
        parent::beforeRender();
    }

    /**
     * @param PageTitle $pageTitle
     * @return void
     */
    protected function setPageTitle(PageTitle $pageTitle) {
        $pageTitle->subTitle = sprintf(_('%d. ročník'), $this->year) . ' ' . $pageTitle->subTitle;
        parent::setPageTitle($pageTitle);
    }
}

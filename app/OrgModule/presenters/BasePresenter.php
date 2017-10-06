<?php

namespace OrgModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\LanguageChooser;
use \ContestNav;
use IContestPresenter;
use Nette\Application\BadRequestException;
use Nette\Diagnostics\Debugger;

/**
 * Presenter keeps chosen contest, year and language in session.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends AuthenticatedPresenter implements IContestPresenter {
    /**
     * include contest,year,series chooser
     */
    use ContestNav;

    protected $role = \ModelRole::ORG;

    /**
     * @var \SeriesCalculator
     */
    protected $seriesCalculator;

    public function injectSeriesCalculator(\SeriesCalculator $seriesCalculator) {
        $this->seriesCalculator = $seriesCalculator;
    }

    protected function startup() {
        $this->startupRedirects();
        parent::startup();
    }

    public function getSelectedAcademicYear() {
        return $this->yearCalculator->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    public function getSubtitle() {
        return $this->getSelectedYear() . '. ' . _('Ročník');
    }

    public function getSelectedContestSymbol() {
        $contest = $this->getSelectedContest();
        return $contest ? $contest->getContestSymbol() : null;
    }

    public function getNavRoot() {
        return 'org.dashboard.default';
    }
}

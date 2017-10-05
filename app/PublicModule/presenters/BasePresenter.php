<?php

namespace PublicModule;

use AuthenticatedPresenter;
use DbNames;
use FKSDB\Components\Controls\LanguageChooser;
use \ContestNav;
use IContestPresenter;
use ModelContestant;
use ModelRole;
use Nette\Application\BadRequestException;

/**
 * Current year of FYKOS.
 *
 * @todo Contest should be from URL and year should be current.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class BasePresenter extends AuthenticatedPresenter implements IContestPresenter {

    use ContestNav;

    const PRESETS_KEY = 'publicPresets';

    protected $role = ModelRole::CONTESTANT;

    /**
     * @var \SeriesCalculator
     */
    protected $seriesCalculator;

    public function injectSeriesCalculator(\SeriesCalculator $seriesCalculator) {
        $this->seriesCalculator = $seriesCalculator;
    }

    protected function startup() {
        parent::startup();
        $this->startupRedirects();
    }

    /** @var ModelContestant|null|false */
    private $contestant = false;


    public function getSelectedAcademicYear() {
        return $this->yearCalculator->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    public function getContestant() {
        if ($this->contestant === false) {
            /**
             * @var $person \ModelPerson
             */
            $person = $this->user->getIdentity()->getPerson();
            $contestant = $person->related(DbNames::TAB_CONTESTANT_BASE, 'person_id')->where(array(
                'contest_id' => $this->getSelectedContest()->contest_id,
                'year' => $this->getSelectedYear()
            ))->fetch();

            $this->contestant = $contestant ? ModelContestant::createFromTableRow($contestant) : null;
        }

        return $this->contestant;
    }

    public function getSelectedContestSymbol() {
        $contest = $this->getSelectedContest();
        return $contest->contest_id ?: null;
    }
    public function getNavRoot() {
        return 'public.dashboard.default';
    }
}

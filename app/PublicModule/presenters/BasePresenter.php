<?php

namespace PublicModule;

use AuthenticatedPresenter;
use DbNames;
use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Components\Controls\LanguageChooser;
use FKSDB\ORM\ModelContestant;
use FKSDB\ORM\ModelRole;
use IContestPresenter;
use Nette\Application\BadRequestException;

/**
 * Current year of FYKOS.
 *
 * @todo Contest should be from URL and year should be current.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends AuthenticatedPresenter implements IContestPresenter {

    const PRESETS_KEY = 'publicPresets';

    /**
     * @persistent
     */
    public $contestId;

    /**
     * @var int
     * @persistent
     */
    public $year;

    /**
     * @persistent
     */
    public $lang;

    protected function startup() {
        parent::startup();
        $this['contestChooser']->syncRedirect();
    }

    protected function createComponentContestChooser($name) {
        $control = new ContestChooser($this->session, $this->yearCalculator, $this->serviceContest);
        $control->setContests(ModelRole::CONTESTANT);
        return $control;
    }

    protected function createComponentLanguageChooser($name) {
        $control = new LanguageChooser($this->session);
        return $control;
    }

    /** @var ModelContestant|null|false */
    private $contestant = false;

    public function getSelectedContest() {
        $contestChooser = $this['contestChooser'];
        if (!$contestChooser->isValid()) {
            throw new BadRequestException('No contests available.', 403);
        }
        return $contestChooser->getContest();
    }

    public function getSelectedYear() {
        $contestChooser = $this['contestChooser'];
        if (!$contestChooser->isValid()) {
            throw new BadRequestException('No contests available.', 403);
        }
        return $contestChooser->getYear();
    }

    public function getSelectedAcademicYear() {
        return $this->yearCalculator->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    public function getSelectedLanguage() {
        $languageChooser = $this['languageChooser'];
        if (!$languageChooser->isValid()) {
            throw new BadRequestException('No languages available.', 403);
        }
        return $languageChooser->getLanguage();
    }

    public function getContestant() {
        if ($this->contestant === false) {
            $person = $this->user->getIdentity()->getPerson();
            $contestant = $person->related(DbNames::TAB_CONTESTANT_BASE, 'person_id')->where(array(
                'contest_id' => $this->getSelectedContest()->contest_id,
                'year' => $this->getSelectedYear()
            ))->fetch();

            $this->contestant = $contestant ? ModelContestant::createFromTableRow($contestant) : null;
        }

        return $this->contestant;
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

    public function getNavRoot() {
        return 'public.dashboard.default';
    }

}

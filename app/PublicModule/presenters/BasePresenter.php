<?php

namespace PublicModule;

use AuthenticatedPresenter;
use DbNames;
use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Components\Controls\LanguageChooser;
use FKSDB\ORM\ModelContestant;
use FKSDB\ORM\ModelPerson;
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
     * @persistent
     */
    public $lang;

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
    protected function createComponentContestChooser(): ContestChooser {
        $control = new ContestChooser($this->session, $this->yearCalculator, $this->serviceContest);
        $control->setContests(ModelRole::CONTESTANT);
        return $control;
    }

    /**
     * @return LanguageChooser
     */
    protected function createComponentLanguageChooser(): LanguageChooser {
        return new LanguageChooser($this->session);
    }

    /** @var ModelContestant|null|false */
    private $contestant = false;

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
     * @return false|ModelContestant|null
     * @throws BadRequestException
     */
    public function getContestant() {
        if ($this->contestant === false) {
            /**
             * @var $person ModelPerson
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

    /**
     * @return string[]
     */
    protected function getNavBarVariant(): array {
        /**
         * @var $contest \FKSDB\ORM\ModelContest
         */
        $contest = $this->serviceContest->findByPrimary($this->contestId);
        if ($contest) {
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

    /**
     * @return string[]
     */
    public function getNavRoots(): array {
        return ['public.dashboard.default'];
    }
}

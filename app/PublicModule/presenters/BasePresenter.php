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

    protected $role = ModelRole::CONTESTANT;

    /**
     * @var int
     * @persistent
     */
    public $series;

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

    protected function createComponentLanguageChooser($name) {
        $control = new LanguageChooser($this->session);
        return $control;
    }

    /** @var ModelContestant|null|false */
    private $contestant = false;


    public function getSelectedAcademicYear() {
        return $this->yearCalculator->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    public function getSelectedLanguage() {
        /**
         * @var $languageChooser LanguageChooser
         */
        $languageChooser = $this['languageChooser'];
        if (!$languageChooser->isValid()) {
            throw new BadRequestException('No languages available.', 403);
        }
        return $languageChooser->getLanguage();
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

}

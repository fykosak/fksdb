<?php

namespace OrgModule;

use AuthenticatedPresenter;
use FKSDB\Components\Controls\LanguageChooser;
use \ContestNav;
use IContestPresenter;
use Nette\Application\BadRequestException;

/**
 * Presenter keeps chosen contest, year and language in session.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends AuthenticatedPresenter implements IContestPresenter{
    /**
     * include contest,year,series chooser
     */
    use ContestNav;

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
     * @var int
     * @persistent
     */
    public $lang;

    protected $role = \ModelRole::ORG;


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
        /**
         * @var $languageChooser LanguageChooser
         */
        $languageChooser = $this['languageChooser'];
        $languageChooser->syncRedirect();
    }

    protected function createComponentLanguageChooser($name) {
        $control = new LanguageChooser($this->session);
        return $control;
    }

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

    public function getSubtitle() {
        return $this->getSelectedYear() . '. ' . _('Ročník');
    }

}

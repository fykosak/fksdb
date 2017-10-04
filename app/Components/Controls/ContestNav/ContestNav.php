<?php
/**
 * @author Michal Červeňák <miso@fykos.cz>
 * trait content contest, year, and series chooser
 */

namespace FKSDB\Components\Controls\ContestNav;

use Nette\Application\UI\Control;
use Nette\Diagnostics\Debugger;
use Nette\Http\Session;
use Nette\Localization\ITranslator;

class ContestNav extends Control {
    /**
     * @var string
     */
    protected $role;
    /**
     * @var \YearCalculator
     */
    protected $yearCalculator;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var \ServiceContest
     */
    protected $serviceContest;

    /**
     * @var \SeriesCalculator
     */
    private $seriesCalculator;

    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * ContestNav constructor.
     * @param \YearCalculator $yearCalculator
     * @param \SeriesCalculator $seriesCalculator
     * @param Session $session
     * @param \ServiceContest $serviceContest
     * @param ITranslator $translator
     */
    public function __construct(
        \YearCalculator $yearCalculator,
        \SeriesCalculator $seriesCalculator,
        Session $session,
        \ServiceContest $serviceContest,
        ITranslator $translator
    ) {
        parent::__construct();
        $this->yearCalculator = $yearCalculator;
        $this->session = $session;
        $this->serviceContest = $serviceContest;
        $this->translator = $translator;
        $this->seriesCalculator = $seriesCalculator;
    }

    /**
     * @param $role
     */
    public function setRole($role) {
        $this->role = $role;
    }

    /**
     * @return ContestChooser
     */
    protected function createComponentContestChooser() {
        $control = new ContestChooser($this->session, $this->yearCalculator, $this->serviceContest);
        $control->setRole($this->role);
        return $control;
    }

    /**
     * @return YearChooser
     */
    protected function createComponentYearChooser() {
        $control = new YearChooser($this->session, $this->yearCalculator, $this->serviceContest);
        $control->setRole($this->role);
        return $control;
    }

    /**
     * @return SeriesChooser
     */
    protected function createComponentSeriesChooser() {
        $control = new SeriesChooser($this->session, $this->seriesCalculator, $this->serviceContest, $this->translator);
        $control->setRole($this->role);
        return $control;
    }

    /**
     * @return LanguageChooser
     */
    protected function createComponentLanguageChooser() {
        $control = new LanguageChooser($this->session);
        return $control;
    }

    /**
     * @return \ModelContest
     */
    public function getSelectedContest() {
        /**
         * @var $contestChooser ContestChooser
         */
        $contestChooser = $this['contestChooser'];
        return $contestChooser->getContest();
    }

    /**
     * @return int
     */
    public function getSelectedYear() {
        /**
         * @var $yearChooser YearChooser
         */
        $yearChooser = $this['yearChooser'];
        return $yearChooser->getYear();
    }

    /**
     * @return mixed
     */
    public function getSelectedLanguage() {
        /**
         * @var $languageChooser LanguageChooser
         */
        $languageChooser = $this['languageChooser'];
        return $languageChooser->getLanguage();
    }

    /**
     * @return int
     */
    public function getSelectedSeries() {
        /**
         * @var $seriesChooser SeriesChooser
         */
        $seriesChooser = $this['yearChooser'];
        return $seriesChooser->getSeries();
    }

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ContestNav.latte');
        $this->template->render();
    }

    /**
     * @param $params object
     * @return object
     * redirect to correct URL
     */
    public function getSyncRedirectParams($params) {
        /**
         * @var $languageChooser LanguageChooser
         */
        // $languageChooser = $this['languageChooser'];
        // $languageChooser->syncRedirect();

        /**
         * @var $contestChooser ContestChooser
         */
        $contestChooser = $this['contestChooser'];
        $redirect = false;
        $redirect = $redirect || $contestChooser->syncRedirect($params);
        /**
         * @var $yearChooser YearChooser
         */
        $yearChooser = $this['yearChooser'];
        $redirect = $redirect || $yearChooser->syncRedirect($params);
        /**
         * @var $seriesChooser SeriesChooser
         */
        $seriesChooser = $this['seriesChooser'];
        $redirect = $redirect || $seriesChooser->syncRedirect($params);
        if ($redirect) {
            return $params;
        } else {
            return null;
        }
    }
}

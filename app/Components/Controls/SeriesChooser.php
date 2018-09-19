<?php

namespace FKSDB\Components\Controls;

use Nette\Application\UI\Control;
use Nette\Http\Session;
use Nette\Localization\ITranslator;
use SeriesCalculator;
use ServiceContest;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SeriesChooser extends Control {

    const SESSION_SECTION = 'seriesPreset';
    const SESSION_KEY = 'series';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var SeriesCalculator
     */
    private $seriesCalculator;

    /**
     * @var ServiceContest
     */
    private $serviceContest;

    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * @var int
     */
    private $series;

    /**
     * @var boolean
     */
    private $initialized = false;

    /**
     * @var boolean
     */
    private $valid;

    function __construct(Session $session, SeriesCalculator $seriesCalculator, ServiceContest $serviceContest, ITranslator $translator) {
        $this->session = $session;
        $this->seriesCalculator = $seriesCalculator;
        $this->serviceContest = $serviceContest;
        $this->translator = $translator;
    }

    public function isValid() {
        $this->init();
        return $this->valid;
    }

    public function getSeries() {
        $this->init();
        return $this->series;
    }

    private function init() {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        if (count($this->getAllowedSeries()) == 0) {
            $this->valid = false;
            return;
        }
        $this->valid = true;

        $session = $this->session->getSection(self::SESSION_SECTION);
        $presenter = $this->getPresenter();
        $contest = $presenter->getSelectedContest();
        $year = $presenter->getSelectedYear();
        $series = null;

        // 1) URL (overrides)
        if (!$series && isset($presenter->series)) {
            $series = $presenter->series;
        }

        // 2) session
        if (!$series && isset($session[self::SESSION_KEY])) {
            $series = $session[self::SESSION_KEY];
        }

        // 3) default (last resort)
        if (!$series || !$this->isValidSeries($series)) {
            $series = $this->seriesCalculator->getCurrentSeries($contest, $year);
        }

        $this->series = $series;

        // for links generation
        $presenter->series = $this->series;

        // remember
        $session[self::SESSION_KEY] = $this->series;
    }

    public function render() {
        if (!$this->isValid()) {
            return;
        }

        $this->template->allowedSeries = $this->getAllowedSeries();
        $this->template->currentSeries = $this->getSeries();

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'SeriesChooser.latte');
        $this->template->render();
    }

    public function handleChange($contestId) {
        $presenter = $this->getPresenter();
        $backupYear = null;
        if (isset($presenter->year)) {
            $backupYear = $presenter->year;
            $presenter->year = null;
        }
        $contest = $this->serviceContest->findByPrimary($contestId);

        $year = $this->calculateYear($this->session, $contest);
        if (isset($presenter->year)) {
            $presenter->year = $backupYear;
        }

        if ($backupYear && $backupYear != $year) {
            $presenter->redirect('this', array('contestId' => $contestId, 'year' => $year));
        } else {
            $presenter->redirect('this', array('contestId' => $contestId));
        }
    }

    /**
     * @return array of int of allowed series
     */
    private function getAllowedSeries() {
        $presenter = $this->getPresenter();
        $contest = $presenter->getSelectedContest();
        $year = $presenter->getSelectedYear();

        $lastSeries = $this->seriesCalculator->getLastSeries($contest, $year);
        if ($lastSeries === null) {
            return array();
        } else {
            return range(1, $lastSeries);
        }
    }

    private function isValidSeries($series) {
        return in_array($series, $this->getAllowedSeries());
    }

    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        $template->setTranslator($this->translator);
        return $template;
    }

}

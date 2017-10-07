<?php

namespace FKSDB\Components\Controls\ContestNav;

use Nette\Application\UI\Control;
use Nette\Diagnostics\Debugger;
use Nette\Http\Session;
use Nette\Localization\ITranslator;
use OrgModule\BasePresenter;
use SeriesCalculator;
use ServiceContest;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SeriesChooser extends Nav {

    const SESSION_SECTION = 'seriesPreset';
    const SESSION_KEY = 'series';

    /**
     * @var SeriesCalculator
     */
    private $seriesCalculator;

    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * @var int
     */
    private $series;
    /**
     * @var integer
     */
    private $year;

    function __construct(Session $session, SeriesCalculator $seriesCalculator, ServiceContest $serviceContest, ITranslator $translator) {
        parent::__construct($session, $serviceContest);
        $this->seriesCalculator = $seriesCalculator;
        $this->translator = $translator;
    }

    public function getSeries() {
        return $this->series;
    }

    protected function init($params) {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        $contest = $this->serviceContest->findByPrimary($params->contestId);
        //Debugger::barDump($params);
        $this->contest = $contest;
        $this->year = $params->year;

        if (count($this->getAllowedSeries()) == 0) {
            $this->series = -1;
            return;
        }

        $series = null;

        // 1) URL (overrides)
        if (isset($params->series) && $params->series != -1) {
            $series = $params->series;
        }

        // 2) session
        $session = $this->session->getSection(self::SESSION_SECTION);
        if ((!$series && $params->series != -1) && isset($session[self::SESSION_KEY])) {
            $series = $session[self::SESSION_KEY];
        }

        // 3) default (last resort)
        if ((!$series && $params->series != -1) || !$this->isValidSeries($series)) {
            $series = $this->seriesCalculator->getCurrentSeries($this->contest);
        }
        // store params
        $this->series = $series ?: -1;

        // remember
        $session[self::SESSION_KEY] = $this->series;
    }

    /**
     * @return void
     */
    public function render() {
        $this->template->allowedSeries = $this->getAllowedSeries();
        $this->template->currentSeries = $this->getSeries();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'SeriesChooser.latte');
        $this->template->render();
    }

    /**
     * @return array of int of allowed series
     */
    private function getAllowedSeries() {
        if ($this->contest === null) {
            return [];
        }
        $lastSeries = $this->seriesCalculator->getLastSeries($this->contest, $this->year);
        if ($lastSeries === null) {
            return [];
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

    public function syncRedirect(&$params) {
        $this->init($params);
        if ($this->series != $params->series) {
            $params->series = $this->series;
            return true;
        }
        return false;
    }
}

<?php

namespace FKSDB\Components\Controls\ContestNav;

use Nette\Application\UI\Control;
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

    private $valid;

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

        if (count($this->getAllowedSeries()) == 0) {
            $this->series = -1;
            return;
        }

        $session = $this->session->getSection(self::SESSION_SECTION);
        $contest = $this->serviceContest->findByPrimary($params->contestId);
        $year = $params->year;
        $series = null;

        // 1) URL (overrides)
        if (isset($params->series)) {
            $series = $params->series;
        }

        // 2) session
        if (!$series && isset($session[self::SESSION_KEY])) {
            $series = $session[self::SESSION_KEY];
        }

        // 3) default (last resort)
        if (!$series || !$this->isValidSeries($series, 0, 0)) {
            $series = $this->seriesCalculator->getCurrentSeries($contest, $year);
        }
        $this->series = $series ?: -1;
        // remember
        $session[self::SESSION_KEY] = $this->series;
    }

    public function render() {
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
    private function getAllowedSeries($contestId = 1, $year = 0) {
        $contest = $this->serviceContest->findByPrimary($contestId);
        $lastSeries = $this->seriesCalculator->getLastSeries($contest, $year);
        if ($lastSeries === null) {
            return [];
        } else {
            return range(1, $lastSeries);
        }
    }

    private function isValidSeries($series, $contestId, $year) {
        return in_array($series, $this->getAllowedSeries($contestId, $year));
    }

    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        $template->setTranslator($this->translator);
        return $template;
    }

    public function syncRedirect($params) {
        $this->init($params);
        if ($this->series != $params->series) {
            return $this->series;
        }
        return null;
    }

}

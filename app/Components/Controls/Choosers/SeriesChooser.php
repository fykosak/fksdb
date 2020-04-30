<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\SeriesCalculator;
use FKSDB\UI\PageTitle;
use FKSDB\UI\Title;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SeriesChooser extends ContestChooser {

    const SESSION_SECTION = 'seriesPreset';
    const SESSION_KEY = 'series';

    /**
     * @var SeriesCalculator
     */
    private $seriesCalculator;

    /**
     * @var int
     */
    private $series;
    /**
     * @var integer
     */
    private $year;

    /**
     * SeriesChooser constructor.
     * @param Container $container
     */
    function __construct(Container $container) {
        parent::__construct($container);
        $this->seriesCalculator = $container->getByType(SeriesCalculator::class);
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

    public function syncRedirect(&$params) {
        $this->init($params);
        if ($this->series != $params->series) {
            $params->series = $this->series;
            return true;
        }
        return false;
    }

    /**
     * @return Title
     */
    protected function getTitle(): Title {
        return new PageTitle(_('Series'));
    }

    /**
     * @return string[]
     */
    protected function getItems() {
        return $this->getAllowedSeries();
    }

    /**
     * @param string $item
     * @return bool
     */
    public function isItemActive($item): bool {
        return $item === $this->getSeries();
    }

    /**
     * @param string|int $item
     * @return string
     */
    public function getItemLabel($item): string {
        return sprintf(_('Series %d'), $item);
    }

    /**
     * @param string|int $item
     * @return string
     * @throws InvalidLinkException
     */
    public function getItemLink($item): string {
        return $this->getPresenter()->link('this', ['series' => $item]);
    }
}

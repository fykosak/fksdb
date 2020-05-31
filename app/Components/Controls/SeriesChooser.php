<?php

namespace FKSDB\Components\Controls;

use FKSDB\SeriesCalculator;
use Nette\Application\BadRequestException;
use Nette\Http\Session;
use OrgModule\SeriesPresenter;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @method SeriesPresenter getPresenter($need = true)
 */
class SeriesChooser extends BaseComponent {

    const SESSION_SECTION = 'seriesPreset';
    const SESSION_KEY = 'series';

    private Session $session;

    private SeriesCalculator $seriesCalculator;

    /**
     * @var int
     */
    private $series;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var bool
     */
    private $valid;

    public function injectPrimary(Session $session, SeriesCalculator $seriesCalculator): void {
        $this->session = $session;
        $this->seriesCalculator = $seriesCalculator;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isValid(): bool {
        $this->init();
        return $this->valid;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getSeries(): int {
        $this->init();
        return $this->series;
    }

    /**
     * @throws \Exception
     */
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
        //  $year = $presenter->getSelectedYear();
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
            $series = $this->seriesCalculator->getCurrentSeries($contest);
        }

        $this->series = $series;

        // for links generation
        $presenter->series = $this->series;

        // remember
        $session[self::SESSION_KEY] = $this->series;
    }

    /**
     * @throws \Exception
     */
    public function render() {
        if (!$this->isValid()) {
            return;
        }

        $this->template->allowedSeries = $this->getAllowedSeries();
        $this->template->currentSeries = $this->getSeries();

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'SeriesChooser.latte');
        $this->template->render();
    }

    /**
     * @return int[] of allowed series
     * @throws BadRequestException
     */
    private function getAllowedSeries(): array {
        $presenter = $this->getPresenter();
        $contest = $presenter->getSelectedContest();
        $year = $presenter->getSelectedYear();

        $lastSeries = $this->seriesCalculator->getLastSeries($contest, $year);
        if ($lastSeries === null) {
            return [];
        } else {
            return range(1, $lastSeries);
        }
    }

    /**
     * @param int $series
     * @return bool
     * @throws BadRequestException
     */
    private function isValidSeries(int $series): bool {
        return in_array($series, $this->getAllowedSeries());
    }
}

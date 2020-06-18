<?php

namespace FKSDB\CoreModule\SeriesPresenter;

use FKSDB\Components\Controls\Choosers\SeriesChooser;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\SeriesCalculator;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;

/**
 * Class SeriesPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
trait SeriesPresenterTrait {

    /**
     * @var int
     * @persistent
     */
    public $series;

    /**
     * @var SeriesCalculator
     */
    protected $seriesCalculator;

    /**
     * @param SeriesCalculator $seriesCalculator
     * @return void
     */
    public function injectSeriesCalculator(SeriesCalculator $seriesCalculator) {
        $this->seriesCalculator = $seriesCalculator;
    }

    final protected function getSeriesCalculator(): SeriesCalculator {
        return $this->seriesCalculator;
    }

    private function getAllowedSeries(): array {
        return $this->getSeriesCalculator()->getAllowedSeries($this->getSelectedContest(), $this->getSelectedYear());
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    protected function seriesTraitStartup() {
        if (+$this->series !== $this->getSelectedSeries()) {
            $this->redirect('this', ['series' => $this->getSelectedSeries()]);
        }
        $control = $this->getComponent('seriesChooser');
        if (!$control instanceof SeriesChooser) {
            throw new BadTypeException(SeriesChooser::class, $control);
        }
        $control->setSeries($this->getSelectedSeries(), $this->getAllowedSeries());
    }

    /**
     * @param int|null $series
     * @return bool
     */
    private function isValidSeries($series): bool {
        return in_array($series, $this->getAllowedSeries());
    }

    /**
     * @return int
     * @throws ForbiddenRequestException
     * @throws BadRequestException
     */
    public function getSelectedSeries(): int {
        $candidate = $this->series ?? $this->getSeriesCalculator()->getLastSeries($this->getSelectedContest(), $this->getSelectedYear());
        if (!$this->isValidSeries($candidate)) {
            throw new ForbiddenRequestException();
        }
        return $candidate;
    }

    protected function createComponentSeriesChooser(): SeriesChooser {
        return new SeriesChooser($this->getContext());
    }

    /**
     * @return Container
     */
    abstract protected function getContext();

    abstract public function getSelectedContest(): ModelContest;

    abstract public function getSelectedYear(): int;
}

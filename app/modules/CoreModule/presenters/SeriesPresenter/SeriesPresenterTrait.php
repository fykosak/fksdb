<?php

namespace FKSDB\CoreModule\SeriesPresenter;

use FKSDB\Components\Controls\Choosers\SeriesChooser;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotFoundException;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\SeriesCalculator;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
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

    protected SeriesCalculator $seriesCalculator;

    public function injectSeriesCalculator(SeriesCalculator $seriesCalculator): void {
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
     * @throws AbortException
     * @throws BadTypeException
     * @throws NotFoundException
     */
    protected function seriesTraitStartup(): void {
        if (is_null($this->series)) {
            $lastSeries = $this->getSeriesCalculator()->getLastSeries($this->getSelectedContest(), $this->getSelectedYear());
            $this->forward('this', ['series' => $lastSeries]);
        }
        $control = $this->getComponent('seriesChooser');
        if (!$control instanceof SeriesChooser) {
            throw new BadTypeException(SeriesChooser::class, $control);
        }
        $control->setSeries($this->getSelectedSeries(), $this->getAllowedSeries());
    }

    private function isValidSeries(?int $series): bool {
        return in_array($series, $this->getAllowedSeries());
    }

    /**
     * @return int
     * @throws NotFoundException
     * @throws AbortException
     */
    public function getSelectedSeries(): ?int {
        if (is_null($this->series)) {
            $this->series = $this->getSeriesCalculator()->getLastSeries($this->getSelectedContest(), $this->getSelectedYear());
        }
        if (!$this->isValidSeries($this->series)) {
            throw new NotFoundException();
        }
        return $this->series;
    }

    public function createComponentSeriesChooser(): SeriesChooser {
        return new SeriesChooser($this->getContext());
    }

    /**
     * @return Container
     */
    abstract protected function getContext();

    /**
     * @return ModelContest
     */
    abstract public function getSelectedContest(): ?ModelContest;

    /**
     * @return int
     */
    abstract public function getSelectedYear(): ?int;
}

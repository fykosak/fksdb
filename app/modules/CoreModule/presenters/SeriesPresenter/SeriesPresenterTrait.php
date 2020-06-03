<?php

namespace FKSDB\CoreModule\SeriesPresenter;

use FKSDB\Components\Controls\Choosers\SeriesChooser;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\SeriesCalculator;
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
    public ?int $series = null;

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
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    protected function seriesTraitStartup(): void {
        if (+$this->series !== $this->getSelectedSeries()) {
            $this->redirect('this', ['series' => $this->getSelectedSeries()]);
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
     * @return int|null
     * @throws ForbiddenRequestException
     */
    public function getSelectedSeries(): ?int {
        $candidate = $this->series ?? $this->getSeriesCalculator()->getLastSeries($this->getSelectedContest(), $this->getSelectedYear());
        if (!$this->isValidSeries($candidate)) {
            throw new ForbiddenRequestException();
        }
        return $candidate;
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

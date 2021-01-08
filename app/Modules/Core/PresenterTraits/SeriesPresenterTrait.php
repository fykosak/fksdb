<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\SeriesChooser;
use FKSDB\Models\SeriesCalculator;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;

/**
 * Class SeriesPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
trait SeriesPresenterTrait {
    use YearPresenterTrait;

    /**
     * @var int
     * @persistent
     */
    public $series;

    private SeriesCalculator $seriesCalculator;

    public function injectSeriesCalculator(SeriesCalculator $seriesCalculator): void {
        $this->seriesCalculator = $seriesCalculator;
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function seriesTraitStartup(): void {
        $this->yearTraitStartup();
        if (!isset($this->series)) {
            $this->redirect('this', array_merge($this->getParameters(), ['series' => $this->selectSeries()]));
        }
    }

    /**
     * @return int
     * @throws ForbiddenRequestException
     */
    private function selectSeries(): int {
        $candidate = $this->seriesCalculator->getLastSeries($this->getSelectedContest(), $this->getSelectedYear());
        if (!$this->isValidSeries($candidate)) {
            throw new ForbiddenRequestException();
        }
        return $candidate;
    }

    private function isValidSeries(?int $series): bool {
        return in_array($series, $this->getAllowedSeries());
    }

    private function getAllowedSeries(): array {
        return $this->seriesCalculator->getAllowedSeries($this->getSelectedContest(), $this->getSelectedYear());
    }

    public function getSelectedSeries(): ?int {
        return $this->series;
    }

    protected function createComponentSeriesChooser(): SeriesChooser {
        return new SeriesChooser($this->getContext(), $this->getSelectedSeries(), $this->getSelectedSeries(), $this->getAllowedSeries());
    }

    /**
     * @return Container
     */
    abstract protected function getContext();
}

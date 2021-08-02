<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\SeriesChooserComponent;
use FKSDB\Models\SeriesCalculator;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

trait SeriesPresenterTrait
{
    use YearPresenterTrait;

    /**
     * @persistent
     */
    public ?int $series = null;

    /**
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function seriesTraitStartup(): void
    {
        $this->yearTraitStartup();
        if (!isset($this->series) || !$this->isValidSeries($this->series)) {
            $this->redirect('this', array_merge($this->getParameters(), ['series' => $this->selectSeries()]));
        }
    }

    /**
     * @return int
     * @throws ForbiddenRequestException
     */
    private function selectSeries(): int
    {
        $candidate = SeriesCalculator::getLastSeries($this->getSelectedContestYear());
        if (!$this->isValidSeries($candidate)) {
            throw new ForbiddenRequestException();
        }
        return $candidate;
    }

    private function isValidSeries(?int $series): bool
    {
        return in_array($series, $this->getAllowedSeries());
    }

    private function getAllowedSeries(): array
    {
        $lastSeries = SeriesCalculator::getLastSeries($this->getSelectedContestYear());
        $range = range(1, $lastSeries);

        // If the year has holiday series, remove posibility to upload 7th series
        // (due to Astrid's structure)
        if (SeriesCalculator::hasHolidaySeries($this->getSelectedContestYear())) {
            $key = array_search('7', $range);
            unset($range[$key]);
        }
        return $range;
    }

    public function getSelectedSeries(): ?int
    {
        return $this->series;
    }

    protected function createComponentSeriesChooser(): SeriesChooserComponent
    {
        return new SeriesChooserComponent($this->getContext(), $this->getSelectedSeries(), $this->getAllowedSeries());
    }
}

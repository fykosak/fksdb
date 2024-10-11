<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Choosers\SeriesChooserComponent;
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
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function seriesTraitStartup(): void
    {
        $this->yearTraitStartup();
        if (!$this->isValidSeries($this->series)) {
            $this->redirect('this', array_merge($this->getParameters(), ['series' => $this->selectSeries()]));
        }
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    private function isValidSeries(?int $series): bool
    {
        if (!isset($series)) {
            return false;
        }
        return in_array($series, $this->getAllowedSeries());
    }

    /**
     * @phpstan-return int[]
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    private function getAllowedSeries(): array
    {
        $lastSeries = $this->getSelectedContestYear()->getLastSeries();
        $range = range(1, $lastSeries);

        // If the year has holiday series, remove posibility to upload 7th series
        // (due to Astrid's structure)
        if ($this->getSelectedContestYear()->hasHolidaySeries()) {
            if (($key = array_search('7', $range)) !== false) {
                unset($range[$key]);
            }
        }
        return $range;
    }

    /**
     * @throws BadRequestException
     */
    private function selectSeries(): int
    {
        $candidates = $this->getAllowedSeries();
        $candidate = end($candidates);
        if (!$candidate) {
            throw new BadRequestException(_('No series available'));
        }
        return $candidate;
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentSeriesChooser(): SeriesChooserComponent
    {
        return new SeriesChooserComponent($this->getContext(), $this->getSelectedSeries(), $this->getAllowedSeries());
    }

    public function getSelectedSeries(): ?int
    {
        return $this->series;
    }
}

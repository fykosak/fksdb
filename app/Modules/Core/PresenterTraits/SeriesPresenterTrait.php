<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\SeriesChooser;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\SeriesCalculator;
use Nette\Application\AbortException;
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
     * @param string $role
     * @return void
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    protected function seriesTraitStartup(string $role): void {
        $this->yearTraitStartup($role);

        $this->role = $role;
        if (!isset($this->series)) {
            $this->redirect('this', array_merge($this->getParameters(), ['series' => $this->selectSeries()]));
        }
    }

    /**
     * @return int
     * @throws ForbiddenRequestException
     * @throws BadTypeException
     */
    private function selectSeries(): int {
        $candidate = $this->seriesCalculator->getLastSeries($this->getSelectedContest(), $this->year);
        if (!$this->isValidSeries($candidate)) {
            throw new ForbiddenRequestException();
        }
        return $candidate;
    }

    /**
     * @param int|null $series
     * @return bool
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    private function isValidSeries(?int $series): bool {
        return in_array($series, $this->getAllowedSeries());
    }

    /**
     * @return array
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    private function getAllowedSeries(): array {
        return $this->seriesCalculator->getAllowedSeries($this->getSelectedContest(), $this->year);
    }

    public function getSelectedSeries(): ?int {
        return $this->series;
    }

    /**
     * @return SeriesChooser
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    protected function createComponentSeriesChooser(): SeriesChooser {
        return new SeriesChooser($this->getContext(), $this->getSelectedContest(), $this->getSelectedYear(), $this->series);
    }

    /**
     * @return Container
     */
    abstract protected function getContext();

    abstract public function getSelectedContest(): ?ModelContest;
}

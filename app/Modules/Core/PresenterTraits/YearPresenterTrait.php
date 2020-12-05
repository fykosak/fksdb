<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\YearChooser;
use FKSDB\YearCalculator;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;

/**
 * Trait YearPresenterTrait
 * @property YearCalculator $yearCalculator
 */
trait YearPresenterTrait {
    use ContestPresenterTrait;

    /**
     * @var int
     * @persistent
     */
    public $year;

    /**
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function yearTraitStartup(): void {
        $this->contestTraitStartup();
        if (!isset($this->year) || !$this->isValidYear($this->year)) {
            $this->redirect('this', array_merge($this->getParameters(), ['year' => $this->selectYear()]));
        }
    }

    /**
     * @return int
     * @throws ForbiddenRequestException
     */
    private function selectYear(): int {
        $candidate = $this->yearCalculator->getCurrentYear($this->getSelectedContest());
        if (!$this->isValidYear($candidate)) {
            throw new ForbiddenRequestException();
        }
        return $candidate;
    }

    private function isValidYear(?int $year): bool {
        return in_array($year, $this->getAvailableItems());
    }

    public function getSelectedYear(): ?int {
        return $this->year;
    }

    protected function getAvailableItems(): array {
        return $this->yearCalculator->getAvailableYears($this->getRole(), $this->getSelectedContest(), $this->getUser());
    }

    public function getSelectedAcademicYear(): int {
        return $this->yearCalculator->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    protected function createComponentYearChooser(): YearChooser {
        return new YearChooser($this->getContext(), $this->getSelectedYear(), $this->getAvailableItems());
    }

    /**
     * @return Container
     */
    abstract protected function getContext();
}

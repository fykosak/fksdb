<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\YearChooser;
use FKSDB\Exceptions\NotImplementedException;
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

    private string $role = YearChooser::ROLE_ORG;

    /**
     * @param string $role
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws NotImplementedException
     */
    protected function yearTraitStartup(string $role): void {
        $this->contestTraitStartup($role);
        $this->role = $role;
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
        return in_array($year, $this->yearCalculator->getAvailableYears($this->role, $this->getSelectedContest(), $this->getUser()));
    }

    public function getSelectedYear(): ?int {
        return $this->year;
    }

    public function getSelectedAcademicYear(): int {
        return $this->yearCalculator->getAcademicYear($this->getSelectedContest(), $this->getSelectedYear());
    }

    protected function createComponentYearChooser(): YearChooser {
        return new YearChooser($this->getContext(), $this->getSelectedYear(), $this->yearCalculator->getAvailableYears($this->role, $this->getSelectedContest(), $this->getUser()));
    }

    /**
     * @return Container
     */
    abstract protected function getContext();
}

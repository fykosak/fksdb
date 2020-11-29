<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\YearChooser;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelContest;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;

trait YearPresenterTrait {
    /**
     * @var int
     * @persistent
     */
    public $year;

    private string $role = YearChooser::ROLE_ORG;

    /**
     * @param string $role
     * @return void
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    protected function yearTraitStartup(string $role): void {
        $this->role = $role;
        if (!isset($this->year)) {
            $this->redirect('this', array_merge($this->getParameters(), ['year' => $this->selectYear()]));
        }
    }

    /**
     * @return int
     * @throws ForbiddenRequestException
     * @throws BadTypeException
     */
    private function selectYear(): int {
        $candidate = $this->yearCalculator->getCurrentYear($this->getSelectedContest());
        if (!$this->isValidYear($candidate)) {
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
    private function isValidYear(?int $series): bool {
        return in_array($series, $this->yearCalculator->getAvailableYears($this->role, $this->getSelectedContest(), $this->getUser()));
    }

    public function getSelectedYear(): ?int {
        return $this->year;
    }

    /**
     * @return YearChooser
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    protected function createComponentYearChooser(): YearChooser {
        return new YearChooser($this->getContext(), $this->year, $this->role, $this->getSelectedContest());
    }

    /**
     * @return Container
     */
    abstract protected function getContext();

    abstract public function getSelectedContest(): ?ModelContest;
}

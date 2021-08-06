<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\YearChooserComponent;
use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\YearCalculator;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\Table\GroupedSelection;
use Nette\InvalidStateException;

/**
 * Trait YearPresenterTrait
 * @property YearCalculator $yearCalculator
 */
trait YearPresenterTrait
{
    use ContestPresenterTrait;

    /**
     * @persistent
     */
    public ?int $year = null;
    private ?ModelContestYear $contestYear;

    /**
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function yearTraitStartup(): void
    {
        $this->contestTraitStartup();
        $contestYear = $this->getSelectedContestYear();
        if (!isset($contestYear) || !$this->isValidContestYear($contestYear)) {
            $this->redirect('this', array_merge($this->getParameters(), ['year' => $this->selectYear()->year]));
        }
    }

    /**
     * @throws ForbiddenRequestException
     */
    private function selectYear(): ModelContestYear
    {
        $candidate = $this->getSelectedContest()->getCurrentContestYear();
        if (!$this->isValidContestYear($candidate)) {
            throw new ForbiddenRequestException();
        }
        return $candidate;
    }

    private function isValidContestYear(?ModelContestYear $contestYear): bool
    {
        if (!$contestYear) {
            return false;
        }
        return (bool)$this->getAvailableYears()->where('year', $contestYear->year)->fetch();
    }

    public function getSelectedContestYear(): ?ModelContestYear
    {
        if (!isset($this->contestYear)) {
            $this->contestYear = $this->getSelectedContest()->getContestYear($this->year);
        }
        return $this->contestYear;
    }

    protected function getAvailableYears(): GroupedSelection
    {
        $contest = $this->getSelectedContest();
        switch ($this->getRole()) {
            case YearChooserComponent::ROLE_ORG:
            case YearChooserComponent::ROLE_ALL:
            case YearChooserComponent::ROLE_SELECTED:
                return $contest->getContestYears();
            case YearChooserComponent::ROLE_CONTESTANT:
                /** @var ModelLogin $login */
                $login = $this->getUser()->getIdentity();
                $years = [];
                if ($login && $login->getPerson()) {
                    $contestants = $login->getPerson()->getContestants($contest);
                    /** @var ModelContestant $contestant */
                    foreach ($contestants as $contestant) {
                        $years[] = $contestant->year;
                    }
                }
                return count($years) ? $contest->getContestYears()->where('year', $years) : $contest->getContestYears(
                )->where('ac_year', YearCalculator::getCurrentAcademicYear());
            default:
                throw new InvalidStateException(sprintf('Role %s is not supported', $this->getRole()));
        }
    }

    protected function createComponentYearChooser(): YearChooserComponent
    {
        return new YearChooserComponent(
            $this->getContext(),
            $this->getSelectedContestYear(),
            $this->getAvailableYears(),
        );
    }
}

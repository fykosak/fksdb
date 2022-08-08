<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\YearChooserComponent;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\YearCalculator;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
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
    private ?ContestYearModel $contestYear;

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

    public function getSelectedContestYear(): ?ContestYearModel
    {
        if (!isset($this->contestYear)) {
            $this->contestYear = $this->getSelectedContest()->getContestYear($this->year);
        }
        return $this->contestYear;
    }

    private function isValidContestYear(?ContestYearModel $contestYear): bool
    {
        if (!$contestYear) {
            return false;
        }
        return (bool)$this->getAvailableYears()->where('year', $contestYear->year)->fetch();
    }

    protected function getAvailableYears(): TypedGroupedSelection
    {
        $contest = $this->getSelectedContest();
        switch ($this->getRole()) {
            case YearChooserComponent::ROLE_ORG:
            case YearChooserComponent::ROLE_ALL:
            case YearChooserComponent::ROLE_SELECTED:
                return $contest->getContestYears();
            case YearChooserComponent::ROLE_CONTESTANT:
                /** @var LoginModel $login */
                $login = $this->getUser()->getIdentity();
                $years = [];
                if ($login && $login->person) {
                    $contestants = $login->person->getContestants($contest);
                    /** @var ContestantModel $contestant */
                    foreach ($contestants as $contestant) {
                        $years[] = $contestant->year;
                    }
                }
                return count($years) ? $contest->getContestYears()->where('year', $years) : $contest->getContestYears()
                    ->where('ac_year', YearCalculator::getCurrentAcademicYear());
            default:
                throw new InvalidStateException(sprintf('Role %s is not supported', $this->getRole()));
        }
    }

    /**
     * @throws ForbiddenRequestException
     */
    private function selectYear(): ContestYearModel
    {
        $candidate = $this->getSelectedContest()->getCurrentContestYear();
        if (!$this->isValidContestYear($candidate)) {
            throw new ForbiddenRequestException();
        }
        return $candidate;
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

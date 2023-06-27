<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\YearChooserComponent;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\InvalidStateException;

trait YearPresenterTrait
{
    use ContestPresenterTrait;

    /**
     * @persistent
     */
    public ?int $year = null;

    /**
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function yearTraitStartup(): void
    {
        $this->contestTraitStartup();
        $contestYear = $this->getSelectedContestYear();
        if (!$this->isValidContestYear($contestYear)) {
            $this->redirect('this', array_merge($this->getParameters(), ['year' => $this->selectYear()->year]));
        }
    }

    public function getSelectedContestYear(): ?ContestYearModel
    {
        static $contestYear;
        if (!isset($contestYear) || $contestYear->year !== $this->year) {
            $contestYear = $this->getSelectedContest()->getContestYear($this->year);
        }
        return $contestYear;
    }

    private function isValidContestYear(?ContestYearModel $contestYear): bool
    {
        if (!isset($contestYear)) {
            return false;
        }
        return (bool)$this->getAvailableYears()->where('year', $contestYear->year)->fetch();
    }

    protected function getAvailableYears(): TypedGroupedSelection
    {
        $contest = $this->getSelectedContest();
        switch ($this->getRole()->value) {
            case PresenterRole::ORG:
            case PresenterRole::ALL:
            case PresenterRole::SELECTED:
                return $contest->getContestYears();
            case PresenterRole::CONTESTANT:
                $person = $this->getLoggedPerson();
                if (!$person) {
                    return $contest->getContestYears()->where('1=0');
                }
                $years = [];
                $contestants = $person->getContestants($contest);
                /** @var ContestantModel $contestant */
                foreach ($contestants as $contestant) {
                    $years[] = $contestant->year;
                }
                if (count($years)) {
                    return $contest->getContestYears()->where('year', $years);
                }
                return $contest->getContestYears()->where('1=0');
            default:
                throw new InvalidStateException(sprintf('Role %s is not supported', $this->getRole()->value));
        }
    }

    private function selectYear(): ContestYearModel
    {
        return $this->getSelectedContest()->getCurrentContestYear();
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

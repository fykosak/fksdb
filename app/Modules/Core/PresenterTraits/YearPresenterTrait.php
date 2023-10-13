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
        try {
            $this->validGivenContestYear();
        } catch (NoContestYearAvailable $exception) {
            $this->redirect('this', array_merge($this->getParameters(), ['year' => $this->selectYear()->year]));
        }
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    public function getSelectedContestYear(): ContestYearModel
    {
        static $contestYear;
        if (!isset($contestYear) || $contestYear->year !== $this->year) {
            $contestYear = $this->getSelectedContest()->getContestYear($this->year);
        }
        if (!$contestYear) {
            throw new NoContestYearAvailable();
        }
        return $contestYear;
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    private function validGivenContestYear(): void
    {
        $contestYear = $this->getSelectedContestYear();
        $contestYear = $this->getAvailableYears()->where('year', $contestYear->year)->fetch();
        if (!$contestYear) {
            throw new NoContestYearAvailable();
        }
    }

    /**
     * @phpstan-return TypedGroupedSelection<ContestYearModel>
     * @throws NoContestAvailable
     */
    protected function getAvailableYears(): TypedGroupedSelection
    {
        $contest = $this->getSelectedContest();
        switch ($this->getRole()->value) {
            case PresenterRole::ORGANIZER:
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
                throw new InvalidStateException(sprintf(_('Role %s is not supported'), $this->getRole()->value));
        }
    }

    /**
     * @throws NoContestAvailable
     */
    private function selectYear(): ContestYearModel
    {
        return $this->getSelectedContest()->getCurrentContestYear();
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentYearChooser(): YearChooserComponent
    {
        return new YearChooserComponent(
            $this->getContext(),
            $this->getSelectedContestYear(),
            $this->getAvailableYears(),
        );
    }
}

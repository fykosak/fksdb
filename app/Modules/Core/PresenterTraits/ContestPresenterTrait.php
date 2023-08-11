<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\ContestChooserComponent;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Services\ContestService;
use Fykosak\NetteORM\TypedSelection;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\Security\User;

/**
 * @property ContestService $contestService
 * @method User getUser()
 */
trait ContestPresenterTrait
{
    /**
     * @persistent
     */
    public ?int $contestId = null;

    /**
     * @throws BadRequestException
     */
    protected function contestTraitStartup(): void
    {
        try {
            $this->validGivenContest();
        } catch (NoContestAvailable $exception) {
            $this->redirect(
                'this',
                array_merge($this->getParameters(), ['contestId' => $this->selectContest()->contest_id])
            );
        }
    }

    /**
     * @throws NoContestAvailable
     */
    public function getSelectedContest(): ContestModel
    {
        static $contest;
        if (!isset($contest) || $contest->contest_id !== $this->contestId) {
            $contest = $this->contestService->findByPrimary($this->contestId);
        }
        if (!$contest) {
            throw new NoContestAvailable();
        }
        return $contest;
    }

    /**
     * @throws NoContestAvailable
     */
    private function validGivenContest(): void
    {
        $contest = $this->getSelectedContest();
        $contest = $this->getAvailableContests()->where('contest_id', $contest->contest_id)->fetch();
        if (!$contest) {
            throw new NoContestAvailable();
        }
    }

    /**
     * @return TypedSelection<ContestModel>
     */
    private function getAvailableContests(): TypedSelection
    {
        $person = $this->getLoggedPerson();

        switch ($this->getRole()->value) {
            case PresenterRole::SELECTED:
                return $this->contestService->getTable()->where('contest_id', $this->contestId);
            case PresenterRole::ALL:
                return $this->contestService->getTable();
            case PresenterRole::CONTESTANT:
                if (!$person) {
                    return $this->contestService->getTable()->where('1=0');
                }
                $contestsIds = [];
                /** @var ContestantModel $contestant */
                foreach ($person->getContestants() as $contestant) {
                    $contestsIds[$contestant->contest_id] = $contestant->contest_id;
                }
                return $this->contestService->getTable()->where('contest_id', array_keys($contestsIds));
            case PresenterRole::ORG:
                if (!$person) {
                    return $this->contestService->getTable()->where('1=0');
                }
                $contestsIds = [];
                foreach ($person->getActiveOrgs() as $org) {
                    $contestsIds[$org->contest_id] = $org->contest_id;
                }
                return $this->contestService->getTable()->where('contest_id', array_keys($contestsIds));
            default:
                throw new InvalidStateException(sprintf(_('Role %s is not supported'), $this->getRole()));
        }
    }

    abstract protected function getRole(): PresenterRole;

    /**
     * @throws NoContestAvailable
     */
    private function selectContest(): ContestModel
    {
        /** @var ContestModel|null $candidate */
        $candidate = $this->getAvailableContests()->fetch();
        if (!$candidate) {
            throw new NoContestAvailable();
        }
        return $candidate;
    }

    /**
     * @throws NoContestAvailable
     */
    protected function createComponentContestChooser(): ContestChooserComponent
    {
        return new ContestChooserComponent(
            $this->getContext(),
            $this->getSelectedContest(),
            $this->getAvailableContests()
        );
    }

    abstract protected function getContext(): Container;
}

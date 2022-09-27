<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\ContestChooserComponent;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\LoginModel;
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
        $contest = $this->getSelectedContest();
        if (!$this->isValidContest($contest)) {
            $this->redirect(
                'this',
                array_merge($this->getParameters(), ['contestId' => $this->selectContest()->contest_id])
            );
        }
    }

    public function getSelectedContest(): ?ContestModel
    {
        static $contest;
        if (!isset($contest) || $contest->contest_id !== $this->contestId) {
            $contest = $this->contestService->findByPrimary($this->contestId);
        }
        return $contest;
    }

    private function isValidContest(?ContestModel $contest): bool
    {
        if (!isset($contest)) {
            return false;
        }
        return (bool)$this->getAvailableContests()->where('contest_id', $contest->contest_id)->fetch();
    }

    /**
     * @return TypedSelection|ContestModel[]
     */
    private function getAvailableContests(): TypedSelection
    {
        /** @var LoginModel|null $login */
        $login = $this->getUser()->getIdentity();

        switch ($this->getRole()->value) {
            case PresenterRole::SELECTED:
                return $this->contestService->getTable()->where('contest_id', $this->contestId);
            case PresenterRole::ALL:
                return $this->contestService->getTable();
            case PresenterRole::CONTESTANT:
                if (!$login || !$login->person) {
                    return $this->contestService->getTable()->where('1=0');
                }
                $contestsIds = [];
                /** @var ContestantModel $contestant */
                foreach ($login->person->getContestants() as $contestant) {
                    $contestsIds[$contestant->contest_id] = $contestant->contest_id;
                }
                return $this->contestService->getTable()->where('contest_id', array_keys($contestsIds));
            case PresenterRole::ORG:
                if (!$login || !$login->person) {
                    return $this->contestService->getTable()->where('1=0');
                }
                $contestsIds = [];
                foreach ($login->person->getActiveOrgs() as $org) {
                    $contestsIds[$org->contest_id] = $org->contest_id;
                }
                return $this->contestService->getTable()->where('contest_id', array_keys($contestsIds));
            default:
                throw new InvalidStateException(sprintf('Role %s is not supported', $this->getRole()));
        }
    }

    abstract protected function getRole(): PresenterRole;

    /**
     * @throws BadRequestException
     */
    private function selectContest(): ContestModel
    {
        /** @var ContestModel $candidate */
        $candidate = $this->getAvailableContests()->fetch();
        if (!$candidate) {
            throw new BadRequestException(_('No contest available'));
        }
        return $candidate;
    }

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

<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\ContestChooserComponent;
use FKSDB\Components\Controls\Choosers\YearChooserComponent;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Services\ContestService;
use Fykosak\NetteORM\TypedSelection;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Security\User;

/**
 * Trait ContestPresenterTrait
 * @property ContestService $contestService
 * @method User getUser()
 */
trait ContestPresenterTrait
{

    /**
     * @persistent
     */
    public ?int $contestId = null;
    private ?ContestModel $contest;

    /**
     * @throws BadRequestException
     */
    protected function contestTraitStartup(): void
    {
        $contest = $this->getSelectedContest();
        if (!isset($contest) || !$this->isValidContest($contest)) {
            $this->redirect(
                'this',
                array_merge($this->getParameters(), ['contestId' => $this->selectContest()->contest_id])
            );
        }
    }

    public function getSelectedContest(): ?ContestModel
    {
        if (!isset($this->contest)) {
            $this->contest = $this->contestService->findByPrimary($this->contestId);
        }
        return $this->contest;
    }

    private function isValidContest(?ContestModel $contest): bool
    {
        if (!$contest) {
            return false;
        }
        return (bool)$this->getAvailableContests()->where('contest_id', $contest->contest_id)->fetch();
    }

    /**
     * @return TypedSelection|ContestModel[]
     */
    private function getAvailableContests(): TypedSelection
    {
        /** @var LoginModel $login */
        $login = $this->getUser()->getIdentity();

        switch ($this->getRole()) {
            case YearChooserComponent::ROLE_SELECTED:
                return $this->contestService->getTable()->where('contest_id', $this->contestId);
            case YearChooserComponent::ROLE_ALL:
                return $this->contestService->getTable();
            case YearChooserComponent::ROLE_CONTESTANT:
            default:
                if (!$login || !$login->person) {
                    return $this->contestService->getTable()->where('1=0');
                }
                $person = $login->person;
                $contestsIds = [];
                foreach ($person->getActiveContestants() as $contestant) {
                    $contestsIds[] = $contestant->contest_id;
                }
                return $this->contestService->getTable()->where('contest_id', $contestsIds);
            case YearChooserComponent::ROLE_ORG:
                if (!$login) {
                    return $this->contestService->getTable()->where('1=0');
                }
                $contestsIds = [];
                foreach ($login->person->getActiveOrgs() as $org) {
                    $contestsIds[] = $org->contest;
                }
                return $this->contestService->getTable()->where('contest_id', $contestsIds);
        }
    }

    abstract protected function getRole(): string;

    /**
     * @throws BadRequestException
     */
    private function selectContest(): ContestModel
    {
        /** @var ContestModel $candidate */
        $candidate = $this->getAvailableContests()->fetch();
        if (!$this->isValidContest($candidate)) {
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

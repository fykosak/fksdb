<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\ContestChooserComponent;
use FKSDB\Components\Controls\Choosers\YearChooserComponent;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Services\ServiceContest;
use Fykosak\NetteORM\TypedTableSelection;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Security\User;

/**
 * Trait ContestPresenterTrait
 * @property ServiceContest $serviceContest
 * @method User getUser()
 */
trait ContestPresenterTrait
{

    /**
     * @persistent
     */
    public ?int $contestId = null;
    private ?ModelContest $contest;

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

    public function getSelectedContest(): ?ModelContest
    {
        if (!isset($this->contest)) {
            $this->contest = $this->serviceContest->findByPrimary($this->contestId);
        }
        return $this->contest;
    }

    private function isValidContest(?ModelContest $contest): bool
    {
        if (!$contest) {
            return false;
        }
        return (bool)$this->getAvailableContests()->where('contest_id', $contest->contest_id)->fetch();
    }

    /**
     * @return TypedTableSelection|ModelContest[]
     */
    private function getAvailableContests(): TypedTableSelection
    {
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();

        switch ($this->getRole()) {
            case YearChooserComponent::ROLE_SELECTED:
                return $this->serviceContest->getTable()->where('contest_id', $this->contestId);
            case YearChooserComponent::ROLE_ALL:
                return $this->serviceContest->getTable();
            case YearChooserComponent::ROLE_CONTESTANT:
            default:
                if (!$login || !$login->getPerson()) {
                    return $this->serviceContest->getTable()->where('1=0');
                }
                $person = $login->getPerson();
                $contestsIds = [];
                foreach ($person->getActiveContestants() as $contestant) {
                    $contestsIds[] = $contestant->contest_id;
                }
                return $this->serviceContest->getTable()->where('contest_id', $contestsIds);
            case YearChooserComponent::ROLE_ORG:
                if (!$login) {
                    return $this->serviceContest->getTable()->where('1=0');
                }
                $contestsIds = [];
                foreach ($login->getActiveOrgs() as $org) {
                    $contestsIds[] = $org->getContest();
                }
                return $this->serviceContest->getTable()->where('contest_id', $contestsIds);
        }
    }

    abstract protected function getRole(): string;

    /**

     * @throws BadRequestException
     */
    private function selectContest(): ModelContest
    {
        /** @var ModelContest $candidate */
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

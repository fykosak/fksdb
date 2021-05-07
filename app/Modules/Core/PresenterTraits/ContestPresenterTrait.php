<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\ContestChooserComponent;
use FKSDB\Components\Controls\Choosers\YearChooserComponent;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Services\ServiceContest;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Security\User;

/**
 * Trait ContestPresenterTrait
 * @property ServiceContest $serviceContest
 * @method User getUser()
 */
trait ContestPresenterTrait {

    /**
     * @persistent
     */
    public ?int $contestId = null;
    private ?ModelContest $contest;

    public function injectServiceContest(ServiceContest $serviceContest): void {
        $this->serviceContest = $serviceContest;
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    protected function contestTraitStartup(): void {
        if (!isset($this->contestId) || !$this->isValidContest()) {
            $this->redirect('this', array_merge($this->getParameters(), ['contestId' => $this->selectContest()->contest_id]));
        }
    }

    /**
     * @return ModelContest
     * @throws BadRequestException
     */
    private function selectContest(): ModelContest {
        $candidates = $this->getAllowedContests();
        if (count($candidates) === 0) {
            throw new BadRequestException(_('No contest available'));
        }
        return reset($candidates);
    }

    private function isValidContest(): bool {
        foreach ($this->getAllowedContests() as $allowedContest) {
            if ($allowedContest->contest_id === $this->getSelectedContest()->contest_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return ModelContest[]
     */
    private function getAllowedContests(): array {
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        switch ($this->getRole()) {
            case YearChooserComponent::ROLE_SELECTED:
                return [$this->serviceContest->findByPrimary($this->contestId)];
            case YearChooserComponent::ROLE_ALL:
                $contests = [];
                foreach ($this->serviceContest->getTable() as $contest) {
                    $contests[] = $contest;
                }
                return $contests;
            case YearChooserComponent::ROLE_CONTESTANT:
                if (!$login || !$login->getPerson()) {
                    return [];
                }
                $person = $login->getPerson();
                $contests = [];
                foreach ($person->getActiveContestants() as $contestant) {
                    $contests[] = $contestant->getContest();
                }
                return $contests;
            case YearChooserComponent::ROLE_ORG:
                if (!$login) {
                    return [];
                }
                $contests = [];
                foreach ($login->getActiveOrgs() as $org) {
                    $contests[] = $org->getContest();
                }
                return $contests;
        }
        return [];
    }

    public function getSelectedContest(): ?ModelContest {
        if (!isset($this->contest)) {
            $this->contest = $this->serviceContest->findByPrimary($this->contestId);
        }
        return $this->contest;
    }

    protected function createComponentContestChooser(): ContestChooserComponent {
        return new ContestChooserComponent($this->getContext(), $this->getSelectedContest(), $this->getAllowedContests());
    }

    abstract protected function getRole(): string;

    abstract protected function getContext(): Container;
}

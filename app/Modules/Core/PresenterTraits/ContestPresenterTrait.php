<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\ContestChooser;
use FKSDB\Components\Controls\Choosers\YearChooser;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Services\ServiceContest;
use Nette\Application\BadRequestException;

/**
 * Trait ContestPresenterTrait
 * @property ServiceContest $serviceContest
 */
trait ContestPresenterTrait {

    /**
     * @var int
     * @persistent
     */
    public $contestId;

    private ?ModelContest $contest;

    public function injectServiceContest(ServiceContest $serviceContest): void {
        $this->serviceContest = $serviceContest;
    }

    /**
     * @param string $role
     * @return void
     * @throws BadRequestException
     */
    protected function contestTraitStartup(): void {
        if (!isset($this->contestId)) {
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

    private function isValidContest(ModelContest $contest): bool {
        foreach ($this->getAllowedContests() as $allowedContest) {
            if ($allowedContest->contest_id === $contest->contest_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return ModelContest[]
     */
    private function getAllowedContests(): array {
        $contestIds = [];
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        switch ($this->getRole()) {
            case YearChooser::ROLE_ALL:
                $contestIds = $this->serviceContest->getTable()->fetchPairs('contest_id', 'contest_id');
                break;
            case YearChooser::ROLE_CONTESTANT:
                if (!$login || !$login->getPerson()) {
                    break;
                }
                $person = $login->getPerson();
                $contestIds = array_keys($person->getActiveContestants($this->yearCalculator));

                break;
            case YearChooser::ROLE_ORG:
                $contestIds = array_keys($login->getActiveOrgs($this->yearCalculator));
                break;
        }
        $contests = [];
        foreach ($contestIds as $id) {
            $contests[] = $this->serviceContest->findByPrimary($id);
        }
        return $contests;
    }

    public function getSelectedContest(): ?ModelContest {
        if (!isset($this->contest)) {
            $this->contest = $this->serviceContest->findByPrimary($this->contestId);
        }
        return $this->contest;
    }

    protected function createComponentContestChooser(): ContestChooser {
        return new ContestChooser($this->getContext(), $this->getSelectedContest(), $this->getAllowedContests());
    }

    abstract protected function getRole(): string;
}

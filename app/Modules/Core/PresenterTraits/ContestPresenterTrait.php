<?php


namespace FKSDB\Modules\Core\PresenterTraits;


use FKSDB\Components\Controls\Choosers\ContestChooser2;
use FKSDB\Components\Controls\Choosers\YearChooser;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Services\ServiceContest;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

trait ContestPresenterTrait {

    /**
     * @var int
     * @persistent
     */
    public $contestId;

    private ?ModelContest $contest;
    private string $role = YearChooser::ROLE_ORG;

    public function injectServiceContest(ServiceContest $serviceContest): void {
        $this->serviceContest = $serviceContest;
    }

    /**
     * @param string $role
     * @return void
     * @throws BadRequestException
     * @throws NotImplementedException
     */
    protected function contestTraitStartup(string $role): void {
        $this->role = $role;
        if (!isset($this->contestId)) {
            $this->redirect('this', array_merge($this->getParameters(), ['contestId' => $this->selectContest()->contest_id]));
        }
    }

    /**
     * @return ModelContest
     * @throws BadRequestException
     * @throws NotImplementedException
     */
    private function selectContest(): ModelContest {
        $candidates = $this->getAllowedContests();
        if (count($candidates) === 0) {
            throw new BadRequestException(_('No contest available'));
        }
        return reset($candidates);
    }

    /**
     * @param ModelContest $contest
     * @return bool
     * @throws NotImplementedException
     */
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
     * @throws NotImplementedException
     */
    private function getAllowedContests(): array {
        $contestIds = [];
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        switch ($this->role) {
            case YearChooser::ROLE_ALL:
                throw new NotImplementedException();
            case YearChooser::ROLE_CONTESTANT:
                $person = $login->getPerson();
                if ($person) {
                    $contests = array_keys($person->getActiveContestants($this->yearCalculator));
                }
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

    /**
     * @return ContestChooser2
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     * @throws NotImplementedException
     */
    protected function createComponentContestChooser2(): ContestChooser2 {
        return new ContestChooser2($this->getContext(), $this->getSelectedContest(), $this->getAllowedContests());
    }
}
<?php

class DispatchPresenter extends AuthenticatedPresenter implements IContestPresenter {

    public function getSelectedContest() {
        return null;
    }

    public function getSelectedYear() {
        return null;
    }

    public function getSelectedAcademicYear() {
        return null;
    }

    public function getSelectedSeries() {
        return null;
    }

    public function renderDefault() {
        /**
         * @var $login ModelLogin
         */
        $login = $this->getPresenter()->getUser()->getIdentity();

        $result = [];
        $roles = $login->getRoles(\ModelLogin::NO_ACL_ROLES);

        /**
         * @var $role \Authorization\Grant
         */
        foreach ($roles as $role) {
            \Nette\Diagnostics\Debugger::barDump($role);
            $result[] = [
                'contest' => $this->serviceContest->findByPrimary($role->getContestId()),
                'role' => $role->getRoleId(),
            ];
        }
        $contests = [];
        foreach ($this->serviceContest->getTable() as $row) {
            $contests[] = $row;
        };
        \Nette\Diagnostics\Debugger::barDump($result);

        $this->template->contests = $contests;
        $this->template->availableCombinations = $result;
    }

    /* private function check(ModelLogin $login, ModelContest $contest, $role) {
         switch ($role) {
             case ModelRole::ORG:
                 foreach ($login->getActiveOrgsContests($this->yearCalculator) as $contestId => $org) {
                     if ($contest->contest_id == $contestId) {
                         return true;
                     }
                 };
                 return false;
             case ModelRole::CONTESTANT:
                 $person = $login->getPerson();
                 if ($person) {
                     foreach ($person->getActiveContestants($this->yearCalculator) as $contestId => $org) {
                         if ($contest->contest_id == $contestId) {
                             return true;
                         }
                     }
                 }
                 return false;
         }
     }*/

    public
    function handleChangeContest($contestId, $role) {
        switch ($role) {
            case 'org':
                $this->redirect(':Org:Dashboard:default', [
                    'contestId' => $contestId,
                ]);
                return;
            case 'contestant':
                $this->redirect(':Public:Dashboard:default', [
                    'contestId' => $contestId,
                ]);
                return;
        }
    }

    public
    function getTitle() {
        return _('Razcestník');
    }

    public
    function getSelectedContestSymbol() {
        return null;
    }

    public
    function getNavRoot() {
        return null;
    }


}

<?php

use FKSDB\ORM\ModelContest;
use FKSDB\ORM\ModelLogin;
use FKSDB\ORM\ModelRole;

class DispatchPresenter extends AuthenticatedPresenter {

    use \LanguageNav;

    public function renderDefault() {
        /**
         * @var $login ModelLogin
         */
        $login = $this->getUser()->getIdentity();
        $query = $this->serviceContest->getTable();
        $result = [];
        foreach ($query as $row) {
            $contest = ModelContest::createFromTableRow($row);
            $symbol = $contest->getContestSymbol();
            $allowed = [];
            foreach ([ModelRole::ORG, ModelRole::CONTESTANT] as $role) {
                $allowed[$role] = $this->check($login, $contest, $role);
            }
            $result[$symbol] = ['data' => $allowed, 'contest' => $contest];
        }
        $this->template->contests = $result;
    }

    private function check(ModelLogin $login, ModelContest $contest, $role) {
        switch ($role) {
            case ModelRole::ORG:
                foreach ($login->getActiveOrgs($this->yearCalculator) as $contestId => $org) {
                    if ($contest->contest_id == $contestId) {
                        return [
                            'link' => $this->link(':Org:Dashboard:default', [
                                'contestId' => $contest->contest_id,
                            ]),
                            'active' => true,
                            'label' => $this->getLabel($contest, $role),
                        ];
                    }
                };
                return [
                    'link' => null,
                    'active' => false,
                    'label' => $this->getLabel($contest, $role),
                ];
            default:
            case ModelRole::CONTESTANT:
                $person = $login->getPerson();
                if ($person) {
                    foreach ($person->getActiveContestants($this->yearCalculator) as $contestId => $org) {
                        if ($contest->contest_id == $contestId) {
                            return [
                                'link' => $this->link(':Public:Dashboard:default', [
                                    'contestId' => $contestId,
                                ]),
                                'active' => true,
                                'label' => $this->getLabel($contest, $role),
                            ];
                        }
                    }
                }
                return [
                    'link' => $this->link(':Public:Register:year', [
                        'contestId' => $contest->contest_id,
                    ]),
                    'active' => true,
                    'label' => $this->getLabel($contest, 'register'),
                ];
        }
    }

    private function getLabel(ModelContest $contest, $role) {
        return $contest->name . ' - ' . _($role);
    }

    public function titleDefault() {
        $this->setTitle(_('Rozcestník'));
        $this->setIcon('fa fa-home');
    }

    public function getNavBarVariant(): array {
        return [null, 'bg-dark navbar-dark'];
    }

    public function getNavRoot() {
        return '';
    }
}

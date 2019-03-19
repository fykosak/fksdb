<?php

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelRole;

/**
 * Class DispatchPresenter
 */
class DispatchPresenter extends AuthenticatedPresenter {

    use \LanguageNav;

    /**
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function renderDefault() {
        /**
         * @var \FKSDB\ORM\Models\ModelLogin $login
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

    /**
     * @param \FKSDB\ORM\Models\ModelLogin $login
     * @param ModelContest $contest
     * @param $role
     * @return array
     * @throws \Nette\Application\UI\InvalidLinkException
     */
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

    /**
     * @param ModelContest $contest
     * @param $role
     * @return string
     */
    private function getLabel(ModelContest $contest, $role) {
        return $contest->name . ' - ' . _($role);
    }

    public function titleDefault() {
        $this->setTitle(_('Rozcestník'));
        $this->setIcon('fa fa-home');
    }

    /**
     * @return array
     */
    public function getNavBarVariant(): array {
        return [null, 'bg-dark navbar-dark'];
    }
}

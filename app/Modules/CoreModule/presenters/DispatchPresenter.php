<?php

namespace FKSDB\Modules\CoreModule;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\UI\PageTitle;
use Nette\Application\UI\InvalidLinkException;

/**
 * Class DispatchPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DispatchPresenter extends AuthenticatedPresenter {
    /** @var array */
    private $contestsProperty;

    /**
     * @throws InvalidLinkException
     */
    public function renderDefault() {
        /**
         * @var ModelLogin $login
         */
        $login = $this->getUser()->getIdentity();
        $person = $login->getPerson();
        $this->template->contestants = $person ? $this->getAllContestants($person) : [];
        $this->template->orgs = $this->getAllOrgs($login);
        $this->template->contestsProperty = $this->getContestsProperty();
    }

    /**
     * @param ModelLogin $login
     * @return array
     * @throws InvalidLinkException
     */
    private function getAllOrgs(ModelLogin $login): array {
        $results = [];
        foreach ($login->getActiveOrgs($this->getYearCalculator()) as $contestId => $org) {
            $results[$contestId] = [
                'link' => $this->link(':Org:Dashboard:default', [
                    'contestId' => $contestId,
                ]),
                'title' => sprintf(_('Organiser %s'), $this->getContestProperty($contestId)['model']->name),
            ];
        }
        return $results;
    }

    public function titleDefault() {
        $this->setPageTitle(new PageTitle(_('Rozcestník'), 'fa fa-home'));
    }

    protected function beforeRender() {
        $this->getPageStyleContainer()->navBarClassName = 'bg-dark navbar-dark';
        parent::beforeRender();
    }

    private function getContestProperty(int $contestId): array {
        return $this->getContestsProperty()[$contestId];
    }

    private function getContestsProperty(): array {
        if (!isset($this->contestsProperty) || is_null($this->contestsProperty)) {
            $query = $this->getServiceContest()->getTable();
            /** @var ModelContest $contest */
            foreach ($query as $contest) {
                $this->contestsProperty[$contest->contest_id] = [
                    'symbol' => $contest->getContestSymbol(),
                    'model' => $contest,
                    'icon' => 'fa fa-' . $contest->getContestSymbol(),
                ];
            }
        }
        return $this->contestsProperty;
    }

    /**
     * @param ModelPerson $person
     * @return array
     * @throws InvalidLinkException
     */
    private function getAllContestants(ModelPerson $person): array {
        $result = [];
        foreach ($person->getActiveContestants($this->getYearCalculator()) as $contestId => $org) {
            $result[$contestId] = [
                'link' => $this->link(':Public:Dashboard:default', [
                    'contestId' => $contestId,
                ]),
                'title' => sprintf(_('Contestant %s'), $this->getContestProperty($contestId)['model']->name),
            ];
        }
        return $result;
    }
}

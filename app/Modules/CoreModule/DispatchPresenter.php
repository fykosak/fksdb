<?php

namespace FKSDB\Modules\CoreModule;

use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\UI\InvalidLinkException;

/**
 * Class DispatchPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DispatchPresenter extends BasePresenter {

    private array $contestsProperty;

    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('Menu'), 'fa fa-home'));
    }

    /**
     * @throws InvalidLinkException
     */
    public function renderDefault(): void {
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        $person = $login->getPerson();
        $this->template->contestants = $person ? $this->getAllContestants($person) : [];
        $this->template->orgs = $this->getAllOrganisers($login);
        $this->template->contestsProperty = $this->getContestsProperty();
    }

    protected function beforeRender(): void {
        $this->getPageStyleContainer()->setNavBarClassName('bg-dark navbar-dark');
        parent::beforeRender();
    }

    /**
     * @param ModelLogin $login
     * @return array
     * @throws InvalidLinkException
     */
    private function getAllOrganisers(ModelLogin $login): array {
        $results = [];
        foreach ($login->getActiveOrgs($this->yearCalculator) as $contestId => $org) {
            $results[$contestId] = [
                'link' => $this->link(':Org:Dashboard:default', [
                    'contestId' => $contestId,
                ]),
                'title' => sprintf(_('Organiser %s'), $this->getContestProperty($contestId)['model']->name),
            ];
        }
        return $results;
    }

    private function getContestProperty(int $contestId): array {
        return $this->getContestsProperty()[$contestId];
    }

    private function getContestsProperty(): array {
        if (!isset($this->contestsProperty)) {
            $this->contestsProperty = [];
            $query = $this->serviceContest->getTable();
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
        foreach ($person->getActiveContestants($this->yearCalculator) as $contestId => $org) {
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

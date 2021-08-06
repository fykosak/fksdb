<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\UI\InvalidLinkException;

class DispatchPresenter extends BasePresenter
{

    private array $contestsProperty;

    public function titleDefault(): PageTitle
    {
        return new PageTitle(_('Menu'), 'fa fa-home');
    }

    /**
     * @throws InvalidLinkException
     */
    final public function renderDefault(): void
    {
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        $person = $login->getPerson();
        $this->template->contestants = $person ? $this->getAllContestants($person) : [];
        $this->template->orgs = $this->getAllOrganisers($login);
        $this->template->contestsProperty = $this->getContestsProperty();
    }

    protected function beforeRender(): void
    {
        $this->getPageStyleContainer()->setNavBarClassName('bg-dark navbar-dark');
        parent::beforeRender();
    }

    /**
     * @param ModelLogin $login
     * @throws InvalidLinkException
     */
    private function getAllOrganisers(ModelLogin $login): array
    {
        $results = [];
        foreach ($login->getActiveOrgs() as $contestId => $org) {
            $results[$contestId] = [
                'link' => $this->link(
                    ':Org:Dashboard:default',
                    [
                        'contestId' => $contestId,
                    ]
                ),
                'title' => sprintf(_('Organiser %s'), $org->getContest()->name),
            ];
        }
        return $results;
    }

    private function getContestProperty(int $contestId): array
    {
        return $this->getContestsProperty()[$contestId];
    }

    private function getContestsProperty(): array
    {
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
     * @throws InvalidLinkException
     */
    private function getAllContestants(ModelPerson $person): array
    {
        $result = [];
        foreach ($person->getActiveContestants() as $contestId => $contestant) {
            $result[$contestId] = [
                'link' => $this->link(
                    ':Public:Dashboard:default',
                    [
                        'contestId' => $contestId,
                    ]
                ),
                'title' => sprintf(_('Contestant %s'), $contestant->getContest()->name),
            ];
        }
        return $result;
    }
}

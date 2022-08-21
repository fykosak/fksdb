<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\InvalidLinkException;

class DispatchPresenter extends BasePresenter
{

    private array $contestsProperty;

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Menu'), 'fa fa-home');
    }

    /**
     * @throws InvalidLinkException
     */
    final public function renderDefault(): void
    {
        /** @var LoginModel $login */
        $login = $this->getUser()->getIdentity();
        $person = $login->person;
        $this->template->contestants = $person ? $this->getAllContestants($person) : [];
        $this->template->orgs = $this->getAllOrganisers($login);
        $this->template->contestsProperty = $this->getContestsProperty();
    }

    /**
     * @throws InvalidLinkException
     */
    private function getAllContestants(PersonModel $person): array
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
                'title' => sprintf(_('Contestant %s'), $contestant->contest->name),
            ];
        }
        return $result;
    }

    /**
     * @throws InvalidLinkException
     */
    private function getAllOrganisers(LoginModel $login): array
    {
        $results = [];
        foreach ($login->person->getActiveOrgs() as $contestId => $org) {
            $results[$contestId] = [
                'link' => $this->link(
                    ':Org:Dashboard:default',
                    [
                        'contestId' => $contestId,
                    ]
                ),
                'title' => sprintf(_('Organiser %s'), $org->contest->name),
            ];
        }
        return $results;
    }

    private function getContestsProperty(): array
    {
        if (!isset($this->contestsProperty)) {
            $this->contestsProperty = [];
            $query = $this->contestService->getTable();
            /** @var ContestModel $contest */
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

    protected function beforeRender(): void
    {
        $this->getPageStyleContainer()->setNavBarClassName('bg-dark navbar-dark');
        $this->getPageStyleContainer()->setNavBrandPath('/images/logo/white.svg');
        parent::beforeRender();
    }
}

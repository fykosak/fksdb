<?php

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Components\Controls\Fyziklani\FinalResultsComponent;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Fyziklani\Ranking\NotClosedTeamException;
use FKSDB\Models\Fyziklani\Ranking\RankingStrategy;
use FKSDB\Models\UI\PageTitle;
use Nette\Utils\Html;

class DiplomasPresenter extends BasePresenter {

    public function titleResults(): void {
        $this->setPageTitle(new PageTitle(_('Final results'), 'fa fa-trophy'));
    }

    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('Calculate ranking'), 'fa fa-calculator'));
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedResults(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.diplomas', 'results'));
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizeDefault(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.diplomas', 'calculate'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    final public function renderDefault(): void {
        $items = [];
        foreach (['A', 'B', 'C'] as $category) {
            $items[$category] = [
                'closed' => $this->getEvent()->getParticipatingTeams()
                    ->where('category', $category)
                    ->where('points IS NOT NULL')
                    ->count(),
                'opened' => $this->getEvent()->getParticipatingTeams()
                    ->where('category', $category)
                    ->where('points IS NULL')
                    ->count(),
            ];
        }
        $this->template->items = $items;
    }

    /**
     * @param string|null $category
     * @throws EventNotFoundException
     * @throws NotClosedTeamException
     */
    public function handleCalculate(string $category = null): void {
        $closeStrategy = new RankingStrategy($this->getEvent(), $this->serviceFyziklaniTeam);
        $log = $closeStrategy($category);
        $this->flashMessage(Html::el()->addHtml(Html::el('h3')->addHtml('Rankin has been saved.'))->addHtml(Html::el('ul')->addHtml($log)), \FKSDB\Modules\Core\BasePresenter::FLASH_SUCCESS);
        $this->redirect('this');
    }

    /**
     * @param string|null $category
     * @return bool
     * @throws EventNotFoundException
     */
    public function isReadyAllToCalculate(string $category = null): bool {
        return $this->serviceFyziklaniTeam->isCategoryReadyForClosing($this->getEvent(), $category);
    }

    /**
     * @return FinalResultsComponent
     * @throws EventNotFoundException
     */
    protected function createComponentResults(): FinalResultsComponent {
        return new FinalResultsComponent($this->getContext(), $this->getEvent());
    }
}

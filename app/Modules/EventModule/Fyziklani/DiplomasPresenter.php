<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Components\Controls\Fyziklani\FinalResultsComponent;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Fyziklani\Ranking\NotClosedTeamException;
use FKSDB\Models\Fyziklani\Ranking\RankingStrategy;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use Nette\Utils\Html;

class DiplomasPresenter extends BasePresenter
{

    public function titleResults(): PageTitle
    {
        return new PageTitle(null, _('Results for diplomas'), 'fa fa-trophy');
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Calculate final ranking'), 'fa fa-calculator');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedResults(): void
    {
        $this->setAuthorized($this->isAllowed('fyziklani.diplomas', 'results'));
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizeDefault(): void
    {
        $this->setAuthorized($this->isAllowed('fyziklani.diplomas', 'calculate'));
    }

    /**
     * @throws EventNotFoundException
     */
    final public function renderDefault(): void
    {
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
     * @throws EventNotFoundException
     * @throws NotClosedTeamException
     */
    public function handleCalculate(string $category = null): void
    {
        $closeStrategy = new RankingStrategy($this->getEvent(), $this->serviceFyziklaniTeam);
        $log = $closeStrategy->close($category);
        $this->flashMessage(
            Html::el()->addHtml(Html::el('h3')->addHtml('Rankin has been saved.'))->addHtml(
                Html::el('ul')->addHtml($log)
            ),
            Message::LVL_SUCCESS
        );
        $this->redirect('this');
    }

    /**
     * @throws EventNotFoundException
     */
    public function isReadyAllToCalculate(?string $category = null): bool
    {
        return $this->serviceFyziklaniTeam->isCategoryReadyForClosing($this->getEvent(), $category);
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentResults(): FinalResultsComponent
    {
        return new FinalResultsComponent($this->getContext(), $this->getEvent());
    }
}

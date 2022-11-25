<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\Game\Diplomas\FinalResultsComponent;
use FKSDB\Components\Game\Diplomas\NotClosedTeamException;
use FKSDB\Components\Game\Diplomas\RankingStrategy;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
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
        $this->setAuthorized($this->isAllowed('game.diplomas', 'results'));
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizeDefault(): void
    {
        $this->setAuthorized($this->isAllowed('game.diplomas', 'calculate'));
    }

    /**
     * @throws EventNotFoundException
     */
    final public function renderDefault(): void
    {
        $items = [];
        foreach (TeamCategory::casesForEvent($this->getEvent()) as $category) {
            $items[$category->value] = [
                'closed' => $this->getEvent()->getParticipatingTeams()
                    ->where('category', $category->value)
                    ->where('points IS NOT NULL')
                    ->count(),
                'opened' => $this->getEvent()->getParticipatingTeams()
                    ->where('category', $category->value)
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
    public function handleCalculate(?TeamCategory $category = null): void
    {
        $closeStrategy = new RankingStrategy($this->getEvent(), $this->teamService);
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
    public function isReadyAllToCalculate(?TeamCategory $category = null): bool
    {
        return $this->teamService->isReadyForClosing($this->getEvent(), $category);
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentResults(): FinalResultsComponent
    {
        return new FinalResultsComponent($this->getContext(), $this->getEvent());
    }
}

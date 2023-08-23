<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Diplomas;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

class FinalResultsComponent extends BaseComponent
{

    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    public function isClosedCategory(TeamCategory $category): bool
    {
        $count = $this->event->getParticipatingTeams()
            ->where('category', $category->value)
            ->where('rank_category IS NULL')
            ->count();
        return $count === 0;
    }

    public function isClosedTotal(): bool
    {
        $count = $this->event->getParticipatingTeams()
            ->where('rank_total IS NULL')
            ->count();
        return $count === 0;
    }

    protected function createComponentResultsCategoryAGrid(): ResultsCategoryGrid
    {
        return new ResultsCategoryGrid($this->event, TeamCategory::from(TeamCategory::A), $this->getContext());
    }

    protected function createComponentResultsCategoryBGrid(): ResultsCategoryGrid
    {
        return new ResultsCategoryGrid($this->event, TeamCategory::from(TeamCategory::B), $this->getContext());
    }

    protected function createComponentResultsCategoryCGrid(): ResultsCategoryGrid
    {
        return new ResultsCategoryGrid($this->event, TeamCategory::from(TeamCategory::C), $this->getContext());
    }

    protected function createComponentResultsTotalGrid(): ResultsTotalGrid
    {
        return new ResultsTotalGrid($this->event, $this->getContext());
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.finalResults.latte');
    }
}

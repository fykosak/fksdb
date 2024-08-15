<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Controls\Inbox\PointPreview\PointsPreviewComponent;
use FKSDB\Components\Controls\Inbox\PointsForm\PointsFormComponent;
use FKSDB\Models\ORM\Models\{TaskContributionType, TaskModel};
use FKSDB\Models\Results\SQLResultsCache;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\InvalidArgumentException;
use Tracy\Debugger;

final class PointsPresenter extends BasePresenter
{
    /**
     * Show all tasks?
     * @persistent
     */
    public ?bool $all = null;
    private SQLResultsCache $resultsCache;

    final public function injectQuarterly(SQLResultsCache $resultsCache): void
    {
        $this->resultsCache = $resultsCache;
    }

    public function titleEntry(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Enter grade series %d'), $this->getSelectedSeries()), 'fas fa-pen');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedEntry(): bool
    {
        return $this->contestAuthorizator->isAllowed(TaskModel::RESOURCE_ID, 'points', $this->getSelectedContest());
    }

    public function titlePreview(): PageTitle
    {
        return new PageTitle(null, _('Points list'), 'fas fa-clipboard-list');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedPreview(): bool
    {
        return $this->contestAuthorizator->isAllowed(TaskModel::RESOURCE_ID, 'points', $this->getSelectedContest());
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    final public function renderEntry(): void
    {
        $this->template->showAll = (bool)$this->all;
        $this->template->hasQuizTask = false;
        /** @var TaskModel $task */
        foreach ($this->getSelectedContestYear()->getTasks($this->getSelectedSeries()) as $task) {
            if ($task->getQuestions()->count('*') > 0) {
                $this->template->hasQuizTask = true;
                break;
            }
        }
    }

    public function handleInvalidate(): void
    {
        try {
            $this->resultsCache->invalidate($this->getSelectedContestYear());
            $this->flashMessage(_('Points invalidated.'), Message::LVL_INFO);
        } catch (\Throwable $exception) {
            $this->flashMessage(_('Error during invalidation.'), Message::LVL_ERROR);
            Debugger::log($exception, Debugger::EXCEPTION);
        }

        $this->redirect('this');
    }

    /**
     * @throws BadRequestException
     */
    public function handleRecalculateAll(): void
    {
        try {
            $this->resultsCache->recalculate($this->getSelectedContestYear());
            $this->flashMessage(_('Points recounted.'), Message::LVL_INFO);
        } catch (InvalidArgumentException $exception) {
            $this->flashMessage(_('Error while recounting.'), Message::LVL_ERROR);
            Debugger::log($exception, Debugger::EXCEPTION);
        }

        $this->redirect('this');
    }

    public function handleCalculateQuizPoints(): void
    {
        try {
            $this->resultsCache->calculateQuizPoints($this->getSelectedContestYear(), $this->getSelectedSeries());
            $this->flashMessage(_('Quiz points calculated.'), Message::LVL_INFO);
        } catch (\Throwable $exception) {
            $this->flashMessage(_('Error during calculation.'), Debugger::EXCEPTION);
            Debugger::log($exception, Debugger::ERROR);
        }
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentPointsForm(): PointsFormComponent
    {
        return new PointsFormComponent(
            $this->getContext(),
            $this->getSelectedContestYear(),
            $this->getSelectedSeries(),
            false,
            $this->all
                ?
                null
                :
                function (TypedGroupedSelection $selection): void {
                    $selection->where(
                        'task_id',
                        $this->getLoggedPerson()
                            ->getTaskContributions(
                                TaskContributionType::from(TaskContributionType::GRADE)
                            )->fetchPairs('task_id', 'task_id')
                    );
                }
        );
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentPointsTableControl(): PointsPreviewComponent
    {
        return new PointsPreviewComponent(
            $this->getContext(),
            $this->getSelectedContestYear(),
            $this->getSelectedSeries()
        );
    }
}

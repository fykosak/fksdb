<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Controls\Inbox\Corrected\CorrectedComponent;
use FKSDB\Components\Controls\Inbox\Corrected\CorrectedFormComponent;
use FKSDB\Components\Controls\Inbox\Inbox\InboxFormComponent;
use FKSDB\Components\Controls\Inbox\SubmitCheck\SubmitCheckComponent;
use FKSDB\Components\Controls\Inbox\SubmitsPreview\SubmitsPreviewComponent;
use FKSDB\Components\Grids\Submits\QuizAnswersGrid;
use FKSDB\Models\ORM\Services\SubmitService;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use FKSDB\Modules\Core\PresenterTraits\SeriesPresenterTrait;
use Fykosak\Utils\UI\PageTitle;

final class InboxPresenter extends BasePresenter
{
    use SeriesPresenterTrait;

    /** @persistent */
    public ?int $id = null;

    private SubmitService $submitService;

    final public function injectSubmitService(SubmitService $submitService): void
    {
        $this->submitService = $submitService;
    }

    public function titleInbox(): PageTitle
    {
        return new PageTitle(null, _('Inbox'), 'fas fa-envelope');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedInbox(): bool
    {
        return $this->contestAuthorizator->isAllowed('submit', null, $this->getSelectedContest());
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of submits'), 'fas fa-list-ul');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->contestAuthorizator->isAllowed('submit', 'list', $this->getSelectedContest());
    }

    public function titleCorrected(): PageTitle
    {
        return new PageTitle(null, _('Corrected'), 'fas fa-file-signature');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedCorrected(): bool
    {
        return $this->contestAuthorizator->isAllowed('submit', 'corrected', $this->getSelectedContest());
    }

    public function titleQuizDetail(): PageTitle
    {
        return new PageTitle(null, _('Quiz detail'), 'fas fa-tasks');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedQuizDetail(): bool
    {
        return $this->authorizedCorrected();
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentInboxForm(): InboxFormComponent
    {
        return new InboxFormComponent(
            $this->getContext(),
            $this->getSelectedContestYear(),
            $this->getSelectedSeries(),
            true
        );
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentCorrectedTable(): CorrectedComponent
    {
        return new CorrectedComponent(
            $this->getContext(),
            $this->getSelectedContestYear(),
            $this->getSelectedSeries()
        );
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentCorrectedForm(): CorrectedFormComponent
    {
        return new CorrectedFormComponent(
            $this->getContext(),
            $this->getSelectedContestYear(),
            $this->getSelectedSeries()
        );
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentCheckControl(): SubmitCheckComponent
    {
        return new SubmitCheckComponent(
            $this->getContext(),
            $this->getSelectedContestYear(),
            $this->getSelectedSeries()
        );
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentSubmitsTableControl(): SubmitsPreviewComponent
    {
        return new SubmitsPreviewComponent(
            $this->getContext(),
            $this->getSelectedContestYear(),
            $this->getSelectedSeries()
        );
    }

    protected function createComponentQuizDetail(): QuizAnswersGrid
    {
        $submit = $this->submitService->findByPrimary($this->id);
        return new QuizAnswersGrid($this->getContext(), $submit, true);
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Inbox\Corrected\CorrectedComponent;
use FKSDB\Components\Controls\Inbox\Corrected\CorrectedFormComponent;
use FKSDB\Components\Controls\Inbox\Inbox\InboxFormComponent;
use FKSDB\Components\Controls\Inbox\SubmitCheck\SubmitCheckComponent;
use FKSDB\Components\Controls\Inbox\SubmitsPreview\SubmitsPreviewComponent;
use FKSDB\Components\Grids\Submits\QuizAnswersGrid;
use FKSDB\Models\ORM\Services\SubmitService;
use FKSDB\Models\Submits\SeriesTable;
use FKSDB\Modules\Core\PresenterTraits\SeriesPresenterTrait;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Authorizator;

class InboxPresenter extends BasePresenter
{
    use SeriesPresenterTrait;

    /** @persistent */
    public ?int $id = null;

    private SeriesTable $seriesTable;
    private SubmitService $submitService;

    final public function injectSeriesTable(SeriesTable $seriesTable, SubmitService $submitService): void
    {
        $this->seriesTable = $seriesTable;
        $this->submitService = $submitService;
    }

    public function titleInbox(): PageTitle
    {
        return new PageTitle(null, _('Inbox'), 'fas fa-envelope');
    }

    public function authorizedInbox(): bool
    {
        return $this->contestAuthorizator->isAllowed('submit', Authorizator::ALL, $this->getSelectedContest());
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of submits'), 'fas fa-list-ul');
    }

    public function authorizedList(): bool
    {
        return $this->contestAuthorizator->isAllowed('submit', 'list', $this->getSelectedContest());
    }

    public function titleCorrected(): PageTitle
    {
        return new PageTitle(null, _('Corrected'), 'fas fa-file-signature');
    }

    public function authorizedCorrected(): bool
    {
        return $this->contestAuthorizator->isAllowed('submit', 'corrected', $this->getSelectedContest());
    }

    public function titleQuizDetail(): PageTitle
    {
        return new PageTitle(null, _('Quiz detail'), 'fas fa-tasks');
    }

    public function authorizedQuizDetail(): bool
    {
        return $this->authorizedCorrected();
    }

    /**
     * @throws UnsupportedLanguageException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function startup(): void
    {
        parent::startup();
        $this->seriesTable->contestYear = $this->getSelectedContestYear();
        $this->seriesTable->series = $this->getSelectedSeries();
    }

    protected function createComponentInboxForm(): InboxFormComponent
    {
        return new InboxFormComponent($this->getContext(), $this->seriesTable);
    }

    protected function createComponentCorrectedTable(): CorrectedComponent
    {
        return new CorrectedComponent($this->getContext(), $this->seriesTable);
    }

    protected function createComponentCorrectedForm(): CorrectedFormComponent
    {
        return new CorrectedFormComponent($this->getContext(), $this->seriesTable);
    }

    protected function createComponentCheckControl(): SubmitCheckComponent
    {
        return new SubmitCheckComponent($this->getContext(), $this->seriesTable);
    }

    protected function createComponentSubmitsTableControl(): SubmitsPreviewComponent
    {
        return new SubmitsPreviewComponent($this->getContext(), $this->seriesTable);
    }

    protected function createComponentQuizDetail(): QuizAnswersGrid
    {
        $submit = $this->submitService->findByPrimary($this->id);
        return new QuizAnswersGrid($this->getContext(), $submit, true);
    }
}

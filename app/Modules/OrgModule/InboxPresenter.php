<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Inbox\Corrected\CorrectedComponent;
use FKSDB\Components\Controls\Inbox\Inbox\InboxFormComponent;
use FKSDB\Components\Controls\Inbox\SubmitCheck\SubmitCheckComponent;
use FKSDB\Components\Controls\Inbox\SubmitsPreview\SubmitsPreviewComponent;
use FKSDB\Components\Grids\Submits\QuizAnswersGrid;
use FKSDB\Models\ORM\Services\SubmitService;
use FKSDB\Models\Submits\SeriesTable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Security\Authorizator;
use FKSDB\Modules\Core\PresenterTraits\SeriesPresenterTrait;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

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

    /* ***************** AUTH ***********************/

    public function authorizedInbox(): void
    {
        $this->setAuthorized(
            $this->contestAuthorizator->isAllowed('submit', Authorizator::ALL, $this->getSelectedContest())
        );
    }

    public function authorizedList(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'list', $this->getSelectedContest()));
    }

    public function authorizedCorrected(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'corrected', $this->getSelectedContest()));
    }

    public function authorizedQuizDetail(): void
    {
        $this->authorizedCorrected();
    }

    /* ***************** TITLES ***********************/

    public function titleInbox(): PageTitle
    {
        return new PageTitle(null, _('Inbox'), 'fa fa-envelope');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of submits'), 'fa fa-list-ul');
    }

    public function titleCorrected(): PageTitle
    {
        return new PageTitle(null, _('Corrected'), 'fa fa-file-signature');
    }

    public function titleQuizDetail(): PageTitle
    {
        return new PageTitle(null, _('Quiz detail'), 'fas fa-tasks');
    }

    /* *********** LIVE CYCLE *************/


    /**
     * @throws ForbiddenRequestException
     * @throws BadRequestException
     */
    protected function startup(): void
    {
        parent::startup();
        $this->seriesTable->contestYear = $this->getSelectedContestYear();
        $this->seriesTable->series = $this->getSelectedSeries();
    }

    /* ******************* COMPONENTS ******************/

    protected function createComponentInboxForm(): InboxFormComponent
    {
        return new InboxFormComponent($this->getContext(), $this->seriesTable);
    }

    protected function createComponentCorrectedFormControl(): CorrectedComponent
    {
        return new CorrectedComponent($this->getContext(), $this->seriesTable);
    }

    protected function createComponentCheckControl(): SubmitCheckComponent
    {
        return new SubmitCheckComponent($this->getContext(), $this->seriesTable);
    }

    protected function createComponentSubmitsTableControl(): SubmitsPreviewComponent
    {
        return new SubmitsPreviewComponent($this->getContext(), $this->seriesTable);
    }

    /**
     * @throws TaskNotFoundException
     * @throws SubmitNotQuizException
     */
    protected function createComponentQuizDetail(): QuizAnswersGrid
    {
        $submit = $this->submitService->findByPrimary($this->id);
        return new QuizAnswersGrid($this->getContext(), $submit, true);
    }

    protected function beforeRender(): void
    {
        switch ($this->getAction()) {
            case 'inbox':
                $this->getPageStyleContainer()->setWidePage();
        }
        parent::beforeRender();
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Charts\Event\Model\GraphComponent;
use FKSDB\Components\Email\EmailProviderForm;
use FKSDB\Components\Event\MassTransition\MassTransitionComponent;
use FKSDB\Components\Grids\EmailsGrid;
use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\Email\Source\Sous\ReminderEmail;
use FKSDB\Models\Email\UIEmailSource;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Models\EmailMessageState;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\Transitions\TransitionsMachineFactory;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;
use Fykosak\Utils\UI\Title;

final class EmailPresenter extends BasePresenter
{
    /** @use EntityPresenterTrait<EmailMessageModel> */
    use EntityPresenterTrait;

    private EmailMessageService $emailMessageService;
    private TransitionsMachineFactory $machineFactory;

    /** @persistent */
    public ?int $source;

    final public function injectServiceEmailMessage(
        EmailMessageService $emailMessageService,
        TransitionsMachineFactory $machineFactory
    ): void {
        $this->emailMessageService = $emailMessageService;
        $this->machineFactory = $machineFactory;
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedTemplate(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(EmailMessageModel::RESOURCE_ID, $this->getSelectedContest()),
            'template',
            $this->getSelectedContest()
        );
    }

    public function renderTemplate(): void
    {
        $this->template->sources = $this->getEmailSources();
        $this->template->source = $this->getEmailSource();
    }

    public function titleTemplate(): PageTitle
    {
        return new PageTitle(null, 'Email templates', 'fas fa-envelope');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedHowTo(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(EmailMessageModel::RESOURCE_ID, $this->getSelectedContest()),
            'howTo',
            $this->getSelectedContest()
        );
    }

    public function titleHowTo(): PageTitle
    {
        return new PageTitle(null, 'How to', 'fas fa-clipboard-question');
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    public function authorizedDetail(): bool
    {
        $authorized = true;
        /** @var ContestModel $contest */
        foreach ($this->contestService->getTable() as $contest) {
            $authorized = $authorized
                && $this->authorizator->isAllowedContest(
                    ContestResourceHolder::fromResource($this->getEntity(), $contest),
                    'detail',
                    $contest
                );
        }
        return $authorized;
    }
    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Detail of email #%s'), $this->getEntity()->getPrimary()),
            'fas fa-envelope-open'
        );
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedTransition(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(EmailMessageModel::RESOURCE_ID, $this->getSelectedContest()),
            'transition',
            $this->getSelectedContest()
        );
    }

    public function titleTransition(): PageTitle
    {
        return new PageTitle(null, _('Transitions'), 'fas fa-envelope-open');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(EmailMessageModel::RESOURCE_ID, $this->getSelectedContest()),
            'list',
            $this->getSelectedContest()
        );
    }
    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of emails'), 'fas fa-mail-bulk');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedDefault(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(EmailMessageModel::RESOURCE_ID, $this->getSelectedContest()),
            'dashboard',
            $this->getSelectedContest()
        );
    }
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Email dashboard'), 'fas fa-mail-bulk');
    }

    public function renderDefault(): void
    {
        $this->template->root = [
            'title' => new Title(null, _('Entities')),
            'items' => [
                'Organizer:Email:list' => [],
                'Organizer:Email:transition' => [],
                'Organizer:Email:template' => [],
                'Organizer:Email:howTo' => [],
            ],
        ];
    }

    /**
     * @return UIEmailSource[]
     */
    protected function getEmailSources(): array //@phpstan-ignore-line
    {
        return [
            new ReminderEmail($this->getContext(), 1),
            new ReminderEmail($this->getContext(), 2),
            new ReminderEmail($this->getContext(), 3),
        ];
    }

    protected function getEmailSource(): ?UIEmailSource //@phpstan-ignore-line
    {
        if (!isset($this->source)) {
            return null;
        }
        return $this->getEmailSources()[$this->source] ?? null;
    }

    protected function getORMService(): EmailMessageService
    {
        return $this->emailMessageService;
    }

    protected function createComponentGrid(): EmailsGrid
    {
        return new EmailsGrid($this->getContext());
    }

    protected function createComponentTemplateForm(): EmailProviderForm //@phpstan-ignore-line
    {
        return new EmailProviderForm($this->getContext(), $this->getEmailSource());
    }

    protected function createComponentStateChart(): GraphComponent //@phpstan-ignore-line
    {
        return new GraphComponent($this->getContext(), $this->machineFactory->getEmailMachine());
    }

    /**
     * @return MassTransitionComponent<EmailMessageModel>
     */
    protected function createComponentMassTransition(): MassTransitionComponent
    {
        return new MassTransitionComponent(
            $this->getContext(),
            $this->machineFactory->getEmailMachine(), //@phpstan-ignore-line
            $this->emailMessageService->getTable()->where('state', EmailMessageState::Ready)
        );
    }
}

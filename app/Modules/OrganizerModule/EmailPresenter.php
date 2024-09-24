<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Email\EmailProviderForm;
use FKSDB\Components\Grids\EmailsGrid;
use FKSDB\Models\Authorization\Resource\PseudoContestResource;
use FKSDB\Models\Email\Source\Sous\ReminderEmailSource;
use FKSDB\Models\Email\UIEmailSource;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;

final class EmailPresenter extends BasePresenter
{
    /** @use EntityPresenterTrait<EmailMessageModel> */
    use EntityPresenterTrait;

    private EmailMessageService $emailMessageService;

    /** @persistent */
    public ?int $source;

    final public function injectServiceEmailMessage(EmailMessageService $emailMessageService): void
    {
        $this->emailMessageService = $emailMessageService;
    }

    /**
     * @return UIEmailSource[]
     */
    protected function getEmailSources(): array //@phpstan-ignore-line
    {
        return [
            new ReminderEmailSource($this->getContext(), 1),
            new ReminderEmailSource($this->getContext(), 2),
            new ReminderEmailSource($this->getContext(), 3),
        ];
    }

    protected function getEmailSource(): ?UIEmailSource //@phpstan-ignore-line
    {
        if (!isset($this->source)) {
            return null;
        }
        return $this->getEmailSources()[$this->source] ?? null;
    }

    public function authorizedDetail(): bool
    {
        $authorized = true;
        /** @var ContestModel $contest */
        foreach ($this->contestService->getTable() as $contest) {
            $authorized = $authorized
                && $this->authorizator->isAllowedContest(
                    $this->getORMService()->getModelClassName()::RESOURCE_ID,
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
            sprintf(_('Detail of e-mail #%s'), $this->getEntity()->getPrimary()),
            'fas fa-envelope-open'
        );
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }
    /**
     * @throws NoContestAvailable
     */
    public function authorizedTemplate(): bool
    {
        return $this->authorizator->isAllowedContest(
            new PseudoContestResource(EmailMessageModel::RESOURCE_ID, $this->getSelectedContest()),
            'template',
            $this->getSelectedContest()
        );
    }

    public function titleTemplate(): PageTitle
    {
        return new PageTitle(null, _('Email templates'), 'fas fa-envelope-open');
    }

    public function renderTemplate(): void
    {
        $this->template->sources = $this->getEmailSources();
        $this->template->source = $this->getEmailSource();
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of emails'), 'fas fa-mail-bulk');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->authorizator->isAllowedContest(
            new PseudoContestResource(EmailMessageModel::RESOURCE_ID, $this->getSelectedContest()),
            'template',
            $this->getSelectedContest()
        );
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
}

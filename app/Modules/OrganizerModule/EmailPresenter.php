<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Grids\EmailsGrid;
use FKSDB\Components\Mail\MailProviderForm;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Mail\MailSource;
use FKSDB\Models\Mail\Sous\Reminder1Mail;
use FKSDB\Models\Mail\Sous\Reminder2Mail;
use FKSDB\Models\Mail\Sous\Reminder3Mail;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

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
     * @return MailSource[]
     */
    protected function getMailSources(): array //@phpstan-ignore-line
    {
        return [
            new Reminder1Mail($this->getContext()),
            new Reminder2Mail($this->getContext()),
            new Reminder3Mail($this->getContext()),
        ];
    }

    protected function getMailSource(): ?MailSource //@phpstan-ignore-line
    {
        if (!isset($this->source)) {
            return null;
        }
        return $this->getMailSources()[$this->source] ?? null;
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

    public function titleTest(): PageTitle
    {
        return new PageTitle(null, '', '');
    }

    public function authorizedDetail(): bool
    {
        $authorized = true;
        /** @var ContestModel $contest */
        foreach ($this->contestService->getTable() as $contest) {
            $authorized = $authorized
                && $this->contestAuthorizator->isAllowed(
                    $this->getORMService()->getModelClassName()::RESOURCE_ID,
                    'detail',
                    $contest
                );
        }
        return $authorized;
    }

    public function authorizedTest(): bool
    {
        return true;
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of emails'), 'fas fa-mail-bulk');
    }

    public function renderTest(): void
    {
        $this->template->sources = $this->getMailSources();
        $this->template->source = $this->getMailSource();
    }

    protected function getORMService(): EmailMessageService
    {
        return $this->emailMessageService;
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    protected function createComponentEditForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentCreateForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentGrid(): EmailsGrid
    {
        return new EmailsGrid($this->getContext());
    }

    protected function createComponentTestForm(): MailProviderForm //@phpstan-ignore-line
    {
        return new MailProviderForm($this->getContext(), $this->getMailSource());
    }

    /**
     * @param Resource|string|null $resource
     * @throws NoContestAvailable
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }
}

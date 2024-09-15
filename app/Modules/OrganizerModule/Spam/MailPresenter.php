<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Spam;

use FKSDB\Components\EntityForms\Spam\MailImportComponent;
use FKSDB\Components\Grids\MailGrid;
use FKSDB\Models\Authorization\Resource\ContestResource;
use FKSDB\Models\Authorization\Resource\PseudoContestResource;
use FKSDB\Models\ORM\Models\PersonMailModel;
use FKSDB\Models\ORM\Services\PersonMailService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\NotImplementedException;

final class MailPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<PersonMailModel> */
    use EntityPresenterTrait;

    private PersonMailService $personMailService;

    public function injectService(PersonMailService $personMailService): void
    {
        $this->personMailService = $personMailService;
    }

    public function authorizedImport(): bool
    {
        return $this->isAllowed(
            new PseudoContestResource(PersonMailModel::RESOURCE_ID, $this->getSelectedContest()),
            'import'
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Mail'), 'fas fa-envelope');
    }

    public function titleImport(): PageTitle
    {
        return new PageTitle(null, _('Mail import'), 'fas fa-download');
    }

    /**
     * @throws NotImplementedException
     */
    protected function createComponentEditForm(): Control
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    protected function createComponentCreateForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentGrid(): Control
    {
        return new MailGrid($this->getContext());
    }

    protected function createComponentImportForm(): MailImportComponent
    {
        return new MailImportComponent($this->getContext());
    }

    protected function getORMService(): PersonMailService
    {
        return $this->personMailService;
    }

    /**
     * @param ContestResource $resource
     * @throws NoContestAvailable
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Spam;

use FKSDB\Components\EntityForms\Spam\MailImportComponent;
use FKSDB\Components\Grids\MailGrid;
use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\ORM\Models\PersonMailModel;
use FKSDB\Models\ORM\Services\PersonMailService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Control;

final class MailPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<PersonMailModel> */
    use EntityPresenterTrait;

    private PersonMailService $personMailService;

    public function injectService(PersonMailService $personMailService): void
    {
        $this->personMailService = $personMailService;
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedImport(): bool
    {
        return $this->isAllowed(
            ContestResourceHolder::fromResourceId(PersonMailModel::RESOURCE_ID, $this->getSelectedContest()),
            'import'
        );
    }

    public function titleImport(): PageTitle
    {
        return new PageTitle(null, _('Mail import'), 'fas fa-download');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->isAllowed(
            ContestResourceHolder::fromResourceId(PersonMailModel::RESOURCE_ID, $this->getSelectedContest()),
            'list'
        );
    }
    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Mail'), 'fas fa-envelope');
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
}

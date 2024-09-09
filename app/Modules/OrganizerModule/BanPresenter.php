<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Grids\BannedPersonGrid;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\BannedPersonModel;
use FKSDB\Models\ORM\Services\BannedPersonService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Application\UI\Control;

final class BanPresenter extends BasePresenter
{
    /** @use EntityPresenterTrait<BannedPersonModel> */
    use EntityPresenterTrait;

    private BannedPersonService $bannedPersonService;

    public function injectService(BannedPersonService $bannedPersonService): void
    {
        $this->bannedPersonService = $bannedPersonService;
    }

    protected function getORMService(): BannedPersonService
    {
        return $this->bannedPersonService;
    }

    /**
     * @param string|BannedPersonModel $resource
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return true;
    }

    protected function createComponentCreateForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentEditForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentGrid(): Control
    {
        return new BannedPersonGrid($this->getContext(), 1024);
    }
}

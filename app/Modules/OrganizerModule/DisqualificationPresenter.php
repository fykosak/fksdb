<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Grids\DisqualifiedPersonGrid;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\DisqualifiedPersonModel;
use FKSDB\Models\ORM\Services\DisqualifiedPersonService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Application\UI\Control;

class DisqualificationPresenter extends BasePresenter
{
    /** @use EntityPresenterTrait<DisqualifiedPersonModel> */
    use EntityPresenterTrait;

    private DisqualifiedPersonService $disqualifiedPersonService;

    public function injectService(DisqualifiedPersonService $disqualifiedPersonService): void
    {
        $this->disqualifiedPersonService = $disqualifiedPersonService;
    }

    protected function getORMService(): DisqualifiedPersonService
    {
        return $this->disqualifiedPersonService;
    }

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
        return new DisqualifiedPersonGrid($this->getContext());
    }
}
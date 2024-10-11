<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Grids\BannedPersonGrid;
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

    protected function createComponentGrid(): Control
    {
        return new BannedPersonGrid($this->getContext(), 1024);
    }
}

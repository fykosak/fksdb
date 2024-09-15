<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Warehouse;

use FKSDB\Components\EntityForms\Warehouse\ItemFormComponent;
use FKSDB\Components\Grids\Warehouse\ItemsGrid;
use FKSDB\Models\Authorization\Resource\ContestResource;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Warehouse\ItemModel;
use FKSDB\Models\ORM\Services\Warehouse\ItemService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Security\Resource;

final class ItemPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<ItemModel> */
    use EntityPresenterTrait;

    private ItemService $itemService;

    public function injectService(ItemService $itemService): void
    {
        $this->itemService = $itemService;
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Items'), 'fas fa-barcode');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Edit item'), 'fas fa-pen');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create item'), 'fas fa-plus');
    }

    /**
     * @throws NoContestAvailable
     */
    protected function createComponentCreateForm(): ItemFormComponent
    {
        return new ItemFormComponent($this->getContext(), $this->getSelectedContest(), null);
    }

    /**
     * @throws NotFoundException
     * @throws GoneException
     * @throws NoContestAvailable
     */
    protected function createComponentEditForm(): ItemFormComponent
    {
        return new ItemFormComponent($this->getContext(), $this->getSelectedContest(), $this->getEntity());
    }

    /**
     * @throws NoContestAvailable
     */
    protected function createComponentGrid(): ItemsGrid
    {
        return new ItemsGrid($this->getContext(), $this->getSelectedContest());
    }

    protected function getORMService(): ItemService
    {
        return $this->itemService;
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

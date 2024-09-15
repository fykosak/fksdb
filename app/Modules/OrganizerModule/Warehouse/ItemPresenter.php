<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Warehouse;

use FKSDB\Components\EntityForms\Warehouse\ItemFormComponent;
use FKSDB\Components\Grids\Warehouse\ItemsGrid;
use FKSDB\Models\Authorization\Resource\PseudoContestResource;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Warehouse\ItemModel;
use FKSDB\Models\ORM\Services\Warehouse\ItemService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;

final class ItemPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<ItemModel> */
    use EntityPresenterTrait;

    private ItemService $itemService;

    public function injectService(ItemService $itemService): void
    {
        $this->itemService = $itemService;
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->isAllowed(
            new PseudoContestResource(ItemModel::RESOURCE_ID, $this->getSelectedContest()),
            'list'
        );
    }
    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Items'), 'fas fa-barcode');
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     * @throws NoContestAvailable
     */
    public function authorizedEdit(): bool
    {
        return $this->isAllowed($this->getEntity(), 'edit');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Edit item'), 'fas fa-pen');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedCreate(): bool
    {
        return $this->isAllowed(
            new PseudoContestResource(ItemModel::RESOURCE_ID, $this->getSelectedContest()),
            'create'
        );
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
}

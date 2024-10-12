<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Warehouse;

use FKSDB\Components\EntityForms\Warehouse\ProductFormComponent;
use FKSDB\Components\Grids\Warehouse\ProductsGrid;
use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Warehouse\WarehouseItemModel;
use FKSDB\Models\ORM\Services\Warehouse\WarehouseItemService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;

final class ProductPresenter extends BasePresenter
{
    /** @use EntityPresenterTrait<WarehouseItemModel> */
    use EntityPresenterTrait;

    private WarehouseItemService $productService;

    /**
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->isAllowed(
            ContestResourceHolder::fromResourceId(WarehouseItemModel::RESOURCE_ID, $this->getSelectedContest()),
            'list'
        );
    }
    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Products'), 'fas fa-dolly');
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     * @throws NoContestAvailable
     */
    public function authorizedEdit(): bool
    {
        return $this->isAllowed(
            ContestResourceHolder::fromResource($this->getEntity(), $this->getSelectedContest()),
            'edit'
        );
    }
    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Edit product'), 'fas fa-pen');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedCreate(): bool
    {
        return $this->isAllowed(
            ContestResourceHolder::fromResourceId(WarehouseItemModel::RESOURCE_ID, $this->getSelectedContest()),
            'create'
        );
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create product'), 'fas fa-plus');
    }

    public function injectService(WarehouseItemService $productService): void
    {
        $this->productService = $productService;
    }

    protected function createComponentCreateForm(): ProductFormComponent
    {
        return new ProductFormComponent($this->getContext(), null);
    }

    /**
     * @throws NotFoundException
     * @throws GoneException
     */
    protected function createComponentEditForm(): ProductFormComponent
    {
        return new ProductFormComponent($this->getContext(), $this->getEntity());
    }

    protected function createComponentGrid(): ProductsGrid
    {
        return new ProductsGrid($this->getContext());
    }

    protected function getORMService(): WarehouseItemService
    {
        return $this->productService;
    }
}

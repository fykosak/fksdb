<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Warehouse;

use FKSDB\Components\EntityForms\Warehouse\ProductFormComponent;
use FKSDB\Components\Grids\Warehouse\ProductsGrid;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Warehouse\ProductModel;
use FKSDB\Models\ORM\Services\Warehouse\ProductService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Security\Resource;

final class ProductPresenter extends BasePresenter
{
    /** @use EntityPresenterTrait<ProductModel> */
    use EntityPresenterTrait;

    private ProductService $productService;

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Products'), 'fas fa-dolly');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Edit product'), 'fas fa-pen');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create product'), 'fas fa-plus');
    }

    public function injectService(ProductService $productService): void
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

    protected function getORMService(): ProductService
    {
        return $this->productService;
    }

    /**
     * @param Resource|string|null $resource
     * @throws NoContestAvailable
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }
}

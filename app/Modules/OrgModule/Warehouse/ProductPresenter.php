<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule\Warehouse;

use FKSDB\Components\EntityForms\Warehouse\ProductFormComponent;
use FKSDB\Components\Grids\Warehouse\ProductsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Models\ORM\Services\Warehouse\ProductService;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

class ProductPresenter extends BasePresenter
{
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
        return new PageTitle(null, _('Create product'), 'fa fa-plus');
    }

    public function injectService(ProductService $productService): void
    {
        $this->productService = $productService;
    }

    protected function createComponentCreateForm(): Control
    {
        return new ProductFormComponent($this->getContext(), null);
    }

    /**
     * @return Control
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    protected function createComponentEditForm(): Control
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
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule\Warehouse;

use FKSDB\Components\Grids\Warehouse\ProductsGrid;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Services\Warehouse\ServiceProduct;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

class ProductPresenter extends BasePresenter
{
    use EntityPresenterTrait;

    private ServiceProduct $serviceProduct;

    public function titleList(): PageTitle
    {
        return new PageTitle(_('Products'), 'fas fa-dolly');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(_('Edit product'), 'fas fa-pen');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(_('Create product'), 'fa fa-plus');
    }

    public function injectService(ServiceProduct $serviceProduct): void
    {
        $this->serviceProduct = $serviceProduct;
    }

    protected function createComponentCreateForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentEditForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentGrid(): ProductsGrid
    {
        return new ProductsGrid($this->getContext());
    }

    protected function getORMService(): ServiceProduct
    {
        return $this->serviceProduct;
    }

    /**
     * @param Resource|string|null $resource
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }
}

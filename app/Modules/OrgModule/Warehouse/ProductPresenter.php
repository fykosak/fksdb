<?php

namespace FKSDB\Modules\OrgModule\Warehouse;

use FKSDB\Components\EntityForms\Warehouse\ProductFormComponent;
use FKSDB\Components\Grids\Warehouse\ProductsGrid;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Models\ORM\Services\Warehouse\ServiceProduct;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

class ProductPresenter extends BasePresenter {

    use EntityPresenterTrait;

    private ServiceProduct $serviceProduct;

    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('Products'), 'fas fa-clipboard-list'));
    }

    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(_('Edit product'), 'fas fa-clipboard-list'));
    }

    public function titleCreate(): void {
        $this->setPageTitle(new PageTitle(_('Create product'), 'fas fa-clipboard-list'));
    }

    public function injectService(ServiceProduct $serviceProduct): void {
        $this->serviceProduct = $serviceProduct;
    }

    protected function createComponentCreateForm(): Control {
        return new ProductFormComponent($this->getContext(), null);
    }

    protected function createComponentEditForm(): Control {
        return new ProductFormComponent($this->getContext(), $this->getEntity());
    }

    protected function createComponentGrid(): ProductsGrid {
        return new ProductsGrid($this->getContext());
    }

    protected function getORMService(): ServiceProduct {
        return $this->serviceProduct;
    }

    /**
     * @param Resource|string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->isAllowed($resource, $privilege);
    }
}

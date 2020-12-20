<?php

namespace FKSDB\Modules\OrgModule\Warehouse;

use FKSDB\Components\Grids\Warehouse\ProductsGrid;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Models\ORM\Services\Warehouse\ServiceProduct;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Class ProductPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ProductPresenter extends BasePresenter {
    use EntityPresenterTrait;

    private ServiceProduct $serviceProduct;

    protected function titleList(): void {
        $this->setPageTitle(new PageTitle(_('Products'), 'fa fa-truck'));
    }

    protected function titleEdit(): void {
        $this->setPageTitle(new PageTitle(_('Edit product'), 'fa fa-truck'));
    }

    protected function titleCreate(): void {
        $this->setPageTitle(new PageTitle(_('Create product'), 'fa fa-truck'));
    }

    public function injectService(ServiceProduct $serviceProduct): void {
        $this->serviceProduct = $serviceProduct;
    }

    protected function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    protected function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }

    protected function createComponentGrid(): ProductsGrid {
        return new ProductsGrid($this->getContext());
    }

    protected function getORMService(): ServiceProduct {
        return $this->serviceProduct;
    }

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->isAllowed($resource, $privilege);
    }
}

<?php

namespace FKSDB\Modules\WarehouseModule;

use FKSDB\Components\Grids\Warehouse\ProductsGrid;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\ORM\Services\Warehouse\ServiceProduct;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Class ProductPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ProductPresenter extends BasePresenter {
    use EntityPresenterTrait;

    private ServiceProduct $serviceProduct;

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

<?php

namespace FKSDB\Modules\WarehouseModule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Warehouse\ProducersGrid;
use FKSDB\Components\Grids\Warehouse\ProductsFromProducerGrid;
use FKSDB\Entity\ModelNotFoundException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\ORM\Models\Warehouse\ModelProducer;
use FKSDB\ORM\Services\Warehouse\ServiceProducer;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Class ProducerPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelProducer getEntity(bool $throw = true)
 */
class ProducerPresenter extends BasePresenter {
    use EntityPresenterTrait;

    private ServiceProducer $serviceProducer;

    public function injectService(ServiceProducer $serviceProducer): void {
        $this->serviceProducer = $serviceProducer;
    }

    /**
     * @return void
     * @throws ModelNotFoundException
     */
    public function renderDetail(): void {
        $this->template->model = $this->getEntity();
    }

    protected function getORMService(): ServiceProducer {
        return $this->serviceProducer;
    }

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->isAllowed($resource, $privilege);
    }


    protected function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    protected function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }

    protected function createComponentGrid(): BaseGrid {
        return new ProducersGrid($this->getContext());
    }

    /**
     * @return ProductsFromProducerGrid
     * @throws ModelNotFoundException
     */
    protected function createComponentProductsFromProducerGrid(): ProductsFromProducerGrid {
        return new ProductsFromProducerGrid($this->getContext(), $this->getEntity());
    }
}

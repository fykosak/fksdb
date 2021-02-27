<?php

namespace FKSDB\Modules\OrgModule\Warehouse;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Warehouse\ProducersGrid;
use FKSDB\Components\Grids\Warehouse\ProductsFromProducerGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Models\ORM\Models\Warehouse\ModelProducer;
use FKSDB\Models\ORM\Services\Warehouse\ServiceProducer;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

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

    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('Producers'), 'fas fa-industry'));
    }

    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(_('Edit producer'), 'fas fa-industry'));
    }

    public function titleCreate(): void {
        $this->setPageTitle(new PageTitle(_('Create producer'), 'fas fa-industry'));
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

    /**
     * @param Resource|string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->isAllowed($resource, $privilege);
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule\Warehouse;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Warehouse\ProducersGrid;
use FKSDB\Components\Grids\Warehouse\ProductsFromProducerGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\Warehouse\ModelProducer;
use FKSDB\Models\ORM\Services\Warehouse\ServiceProducer;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

/**
 * @method ModelProducer getEntity(bool $throw = true)
 */
class ProducerPresenter extends BasePresenter
{
    use EntityPresenterTrait;

    private ServiceProducer $serviceProducer;

    public function injectService(ServiceProducer $serviceProducer): void
    {
        $this->serviceProducer = $serviceProducer;
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(_('Producers'), 'fas fa-store-alt');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(_('Edit producer'), 'fas fa-pen');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(_('Create producer'), 'fa fa-plus');
    }

    /**
     * @throws ModelNotFoundException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    protected function getORMService(): ServiceProducer
    {
        return $this->serviceProducer;
    }

    protected function createComponentCreateForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentEditForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentGrid(): BaseGrid
    {
        return new ProducersGrid($this->getContext());
    }

    /**
     * @throws ModelNotFoundException
     */
    protected function createComponentProductsFromProducerGrid(): ProductsFromProducerGrid
    {
        return new ProductsFromProducerGrid($this->getContext(), $this->getEntity());
    }

    /**
     * @param Resource|string|null $resource
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule\Warehouse;

use FKSDB\Components\Grids\Warehouse\ProducersGrid;
use FKSDB\Components\Grids\Warehouse\ProductsFromProducerGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\Warehouse\ProducerModel;
use FKSDB\Models\ORM\Services\Warehouse\ProducerService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

final class ProducerPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<ProducerModel> */
    use EntityPresenterTrait;

    private ProducerService $producerService;

    public function injectService(ProducerService $producerService): void
    {
        $this->producerService = $producerService;
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Producers'), 'fas fa-store-alt');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Edit producer'), 'fas fa-pen');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create producer'), 'fas fa-plus');
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    protected function getORMService(): ProducerService
    {
        return $this->producerService;
    }

    protected function createComponentCreateForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentEditForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentGrid(): ProducersGrid
    {
        return new ProducersGrid($this->getContext());
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
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

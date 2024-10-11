<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Warehouse;

use FKSDB\Components\Grids\Warehouse\ProducersGrid;
use FKSDB\Components\Grids\Warehouse\ProductsFromProducerGrid;
use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Warehouse\ProducerModel;
use FKSDB\Models\ORM\Services\Warehouse\ProducerService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;

final class ProducerPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<ProducerModel> */
    use EntityPresenterTrait;

    private ProducerService $producerService;

    public function injectService(ProducerService $producerService): void
    {
        $this->producerService = $producerService;
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->isAllowed(
            ContestResourceHolder::fromResourceId(ProducerModel::RESOURCE_ID, $this->getSelectedContest()),
            'list'
        );
    }
    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Producers'), 'fas fa-store-alt');
    }

    /**
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NotFoundException
     */
    public function authorizedEdit(): bool
    {
        return $this->isAllowed(
            ContestResourceHolder::fromResource($this->getEntity(), $this->getSelectedContest()),
            'edit'
        );
    }
    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Edit producer'), 'fas fa-pen');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedCreate(): bool
    {
        return $this->isAllowed(
            ContestResourceHolder::fromResourceId(ProducerModel::RESOURCE_ID, $this->getSelectedContest()),
            'create'
        );
    }
    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create producer'), 'fas fa-plus');
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     * @throws NoContestAvailable
     */
    public function authorizedDetail(): bool
    {
        return $this->isAllowed(
            ContestResourceHolder::fromResource($this->getEntity(), $this->getSelectedContest()),
            'detail'
        );
    }

    public function titleDetail(): PageTitle
    {
        return new PageTitle(null, _('Detail of producer'), 'fas fa-plus');
    }
    /**
     * @throws NotFoundException
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

    protected function createComponentGrid(): ProducersGrid
    {
        return new ProducersGrid($this->getContext());
    }

    /**
     * @throws NotFoundException
     * @throws GoneException
     */
    protected function createComponentProductsFromProducerGrid(): ProductsFromProducerGrid
    {
        return new ProductsFromProducerGrid($this->getContext(), $this->getEntity());
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Modules\ShopModule;

use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Warehouse\WarehouseItemModel;
use FKSDB\Models\ORM\Services\Warehouse\WarehouseItemService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Fykosak\Utils\UI\PageTitle;

class MerchPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<WarehouseItemModel> */
    use EntityPresenterTrait;

    private WarehouseItemService $productService;

    public function inject(WarehouseItemService $productService): void
    {
        $this->productService = $productService;
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    public function authorizedDefault(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResource($this->getEntity(), $this->getContest()),
            'shop',
            $this->getContest()
        );
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, $this->getEntity()->getName()->getText($this->translator->lang));
    }

    protected function getORMService(): WarehouseItemService
    {
        return $this->productService;
    }

    public function renderDefault(): void
    {
        $this->template->model = $this->getEntity();
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule\Warehouse;

use FKSDB\Components\Grids\Warehouse\ItemsGrid;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Services\Warehouse\ServiceItem;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

class ItemPresenter extends BasePresenter
{
    use EntityPresenterTrait;

    private ServiceItem $serviceItem;

    public function injectService(ServiceItem $serviceItem): void
    {
        $this->serviceItem = $serviceItem;
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(_('Items'), 'fa fa-barcode');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(_('Edit item'), 'fas fa-pen');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(_('Create item'), 'fa fa-plus');
    }

    protected function createComponentCreateForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentEditForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentGrid(): ItemsGrid
    {
        return new ItemsGrid($this->getContext(), $this->getSelectedContest());
    }

    protected function getORMService(): ServiceItem
    {
        return $this->serviceItem;
    }

    /**
     * @param Resource|string|null $resource
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }
}

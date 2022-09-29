<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule\Warehouse;

use FKSDB\Components\EntityForms\Warehouse\ItemFormComponent;
use FKSDB\Components\Grids\Warehouse\ItemsGrid;
use FKSDB\Models\ORM\Services\Warehouse\ItemService;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

class ItemPresenter extends BasePresenter
{
    use EntityPresenterTrait;

    private ItemService $itemService;

    public function injectService(ItemService $itemService): void
    {
        $this->itemService = $itemService;
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Items'), 'fa fa-barcode');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Edit item'), 'fas fa-pen');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create item'), 'fa fa-plus');
    }

    protected function createComponentCreateForm(): Control
    {
        return new ItemFormComponent($this->getContext(), $this->getSelectedContest(), null);
    }

    protected function createComponentEditForm(): Control
    {
        return new ItemFormComponent($this->getContext(), $this->getSelectedContest(), $this->getEntity());
    }

    protected function createComponentGrid(): ItemsGrid
    {
        return new ItemsGrid($this->getContext(), $this->getSelectedContest());
    }

    protected function getORMService(): ItemService
    {
        return $this->itemService;
    }

    /**
     * @param Resource|string|null $resource
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }
}

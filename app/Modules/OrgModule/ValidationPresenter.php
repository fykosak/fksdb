<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\DataTesting\PersonTestComponent;
use FKSDB\Components\Grids\DataTesting\PersonsGrid;
use Fykosak\Utils\UI\PageTitle;

final class ValidationPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Data validation'), 'fas fa-clipboard-check');
    }

    public function authorizedDefault(): bool
    {
        return $this->contestAuthorizator->isAllowed('person', 'validation');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('All tests'), 'fas fa-tasks');
    }

    public function authorizedList(): bool
    {
        return $this->authorizedDefault();
    }

    public function titlePreview(): PageTitle
    {
        return new PageTitle(null, _('Select test'), 'fas fa-check');
    }

    public function authorizedPreview(): bool
    {
        return $this->authorizedDefault();
    }

    protected function createComponentGrid(): PersonsGrid
    {
        return new PersonsGrid($this->getContext());
    }

    protected function createComponentValidationControl(): PersonTestComponent
    {
        return new PersonTestComponent($this->getContext());
    }
}

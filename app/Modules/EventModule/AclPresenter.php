<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Grids\Acl\EventAclGrid;
use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use Fykosak\Utils\UI\PageTitle;

final class AclPresenter extends BasePresenter
{
    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromOwnResource($this->getEvent()),
            'acl',
            $this->getEvent()
        );
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('ACL list'), 'fas fa-user-lock');
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): EventAclGrid
    {
        return new EventAclGrid($this->getContext(), $this->getEvent());
    }
}

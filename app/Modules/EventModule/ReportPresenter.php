<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Components\DataTest\TestsList;
use FKSDB\Models\Authorization\Resource\PseudoEventResource;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\Utils\UI\PageTitle;

final class ReportPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Errors & warnings'), 'fas fa-triangle-exclamation');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): bool
    {
        return $this->authorizator->isAllowedEvent(
            new PseudoEventResource(EventModel::RESOURCE_ID, $this->getEvent()),
            'report',
            $this->getEvent()
        );
    }

    /**
     * @throws EventNotFoundException
     */
    final public function renderDefault(): void
    {
        set_time_limit(-1);
        $this->template->event = $this->getEvent();
    }

    /**
     * @phpstan-return TestsList<EventModel>
     */
    protected function createComponentTests(): TestsList
    {
        return new TestsList($this->getContext(), DataTestFactory::getEventTests($this->getContext()), true);
    }
}

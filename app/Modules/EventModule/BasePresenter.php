<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Choosers\EventChooserComponent;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

abstract class BasePresenter extends AuthenticatedPresenter
{
    /** @persistent */
    public ?int $eventId = null;
    protected EventService $eventService;
    protected EventDispatchFactory $eventDispatchFactory;

    final public function injectEventBase(EventService $eventService, EventDispatchFactory $eventDispatchFactory): void
    {
        $this->eventService = $eventService;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    public function isAuthorized(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }
        return parent::isAuthorized();
    }

    /**
     * @param Resource|string|null $resource
     * Check if has contest permission or is Event org
     * @throws EventNotFoundException
     */
    public function isAllowed($resource, ?string $privilege): bool
    {
        return $this->eventAuthorizator->isAllowed($resource, $privilege, $this->getEvent());
    }

    /**
     * @throws NotImplementedException
     * @throws ForbiddenRequestException
     */
    protected function startup(): void
    {
        if (!$this->isEnabled()) {
            throw new NotImplementedException();
        }
        parent::startup();
    }

    protected function isEnabled(): bool
    {
        return true;
    }

    /**
     * @throws EventNotFoundException
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     */
    protected function getDummyHolder(): BaseHolder
    {
        static $holder;
        if (!isset($holder) || $holder->event->event_id !== $this->getEvent()->event_id) {
            $holder = $this->eventDispatchFactory->getDummyHolder($this->getEvent());
        }
        return $holder;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function getEvent(): EventModel
    {
        static $event;
        if (!isset($event) || $event->event_id !== $this->eventId) {
            $event = $this->eventService->findByPrimary($this->eventId);
            if (!$event) {
                throw new EventNotFoundException();
            }
        }
        return $event;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function getDefaultSubTitle(): ?string
    {
        return $this->getEvent()->name;
    }

    /**
     * @throws EventNotFoundException
     * @throws BadRequestException
     */
    protected function beforeRender(): void
    {
        $this->getPageStyleContainer()->styleIds[] = 'event event-type-' . $this->getEvent()->event_type_id;
        switch ($this->getEvent()->event_type_id) {
            case 1:
                $this->getPageStyleContainer()->setNavBarClassName('bg-fof navbar-dark');
                $this->getPageStyleContainer()->setNavBrandPath('/images/logo/white.svg');
                break;
            case 9:
                $this->getPageStyleContainer()->setNavBarClassName('bg-fol navbar-dark');
                break;
            case 17:
                $this->getPageStyleContainer()->setNavBarClassName('bg-ctyrboj navbar-dark');
                $this->getPageStyleContainer()->setNavBrandPath('/images/logo/white.svg');
                break;
            default:
                $this->getPageStyleContainer()->setNavBarClassName('bg-light navbar-light');
        }
        parent::beforeRender();
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentEventChooser(): EventChooserComponent
    {
        return new EventChooserComponent($this->getContext(), $this->getEvent());
    }

    /**
     * @return string[]
     */
    protected function getNavRoots(): array
    {
        return ['Event.Dashboard.default'];
    }
}

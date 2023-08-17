<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Choosers\EventChooserComponent;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\ComponentReflection;
use Nette\Security\Resource;

abstract class BasePresenter extends \FKSDB\Modules\Core\BasePresenter
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

    /**
     * @param ComponentReflection|\ReflectionMethod $element
     * @throws \ReflectionException
     * @throws ForbiddenRequestException
     */
    public function checkRequirements($element): void
    {
        if (!$this->isEnabled()) {
            throw new ForbiddenRequestException();
        }
        parent::checkRequirements($element);
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

    protected function isEnabled(): bool
    {
        return true;
    }

    /**
     * @throws EventNotFoundException
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
    protected function getSubTitle(): ?string
    {
        return $this->getEvent()->getName()->getText('cs');//TODO!
    }

    /**
     * @throws EventNotFoundException
     */
    protected function getStyleId(): string
    {
        return 'event-type-' . $this->getEvent()->event_type_id;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentEventChooser(): EventChooserComponent
    {
        return new EventChooserComponent($this->getContext(), $this->getEvent());
    }

    /**
     * @phpstan-return string[]
     */
    protected function getNavRoots(): array
    {
        return ['Event.Dashboard.default#application', 'Event.Dashboard.default#other'];
    }
}

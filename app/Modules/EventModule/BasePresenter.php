<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Choosers\EventChooser;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
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
     * @throws EventNotFoundException
     */
    protected function beforeRender(): void
    {
        parent::beforeRender();
        $this->template->event = $this->getEvent();
    }

    /**
     * @param Resource|string|null $resource
     * Check if has contest permission or is Event organizer
     * @throws EventNotFoundException
     */
    final public function isAllowed($resource, ?string $privilege): bool
    {
        return $this->eventAuthorizator->isAllowed($resource, $privilege, $this->getEvent());
    }

    protected function isEnabled(): bool
    {
        return true;
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
        return $this->getEvent()->getName()->getText($this->translator->lang);//TODO!
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
    protected function createComponentEventChooser(): EventChooser
    {
        return new EventChooser($this->getContext(), $this->getEvent());
    }

    /**
     * @phpstan-return string[]
     */
    protected function getNavRoots(): array
    {
        return ['Event.Dashboard.default#application', 'Event.Dashboard.default#other'];
    }

    /**
     * @throws EventNotFoundException
     * @phpstan-return string[]
     */
    public function formatTemplateFiles(): array
    {
        $files = parent::formatTemplateFiles();

        return [
            str_replace('.latte', '.' . $this->getEvent()->event_type->getSymbol() . '.latte', $files[0]),
            ...$files,
        ];
    }
}

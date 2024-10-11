<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Choosers\EventChooser;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\Transitions\TransitionsMachineFactory;
use Fykosak\Utils\UI\Title;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\ComponentReflection;

abstract class BasePresenter extends \FKSDB\Modules\Core\BasePresenter
{
    /** @persistent */
    public ?int $eventId = null;
    protected EventService $eventService;
    protected TransitionsMachineFactory $eventDispatchFactory;

    final public function injectEventBase(
        EventService $eventService,
        TransitionsMachineFactory $eventDispatchFactory
    ): void {
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
        return $this->getEvent()->getName()->getText($this->translator->lang); // @phpstan-ignore-line
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

    protected function getNavRoots(): array
    {
        return [
            [
                'title' => new Title(null, _('Applications')),
                'items' => [
                    'Event:Team:detailedList' => [],
                    'Event:Team:default' => [],
                    'Event:Team:mass' => [],
                    'Event:Team:create' => [],
                    #single
                    'Event:Application:default' => [],
                    'Event:Application:mass' => [],
                    'Event:Application:import' => [],
                    'Event:Attendance:search' => [],
                ],
            ],
            [
                'title' => new Title(null, _('Others')),
                'items' => [
                    'Event:Report:default' => [],
                    'Event:EventOrganizer:list' => [],
                    'EventGame:Dashboard:default' => [],
                    'EventSchedule:Dashboard:default' => [],
                    'Event:Chart:list' => [],
                    'Event:Dispatch:default' => [],
                    'Event:Acl:default' => [],
                    'Event:Dashboard:default' => [],
                ],
            ],
        ];
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

<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventsModule\Game;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Modules\EventsModule\BasePresenter as EventBasePresenter;

abstract class BasePresenter extends EventBasePresenter
{
    public const GAME_EVENTS = [1, 17];

    protected TeamService2 $teamService;
    protected SubmitService $submitService;

    final public function injectGameBase(SubmitService $submitService, TeamService2 $teamService): void
    {
        $this->submitService = $submitService;
        $this->teamService = $teamService;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function beforeRender(): void
    {
        $this->template->event = $this->getEvent();
        parent::beforeRender();
    }

    /**
     * @throws EventNotFoundException
     */
    protected function isEnabled(): bool
    {
        return in_array($this->getEvent()->event_type_id, self::GAME_EVENTS);
    }

    /**
     * @phpstan-return string[]
     */
    protected function getNavRoots(): array
    {
        return ['Game.Dashboard.default', 'Game.Statistics.table'];
    }
}

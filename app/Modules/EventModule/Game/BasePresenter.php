<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Modules\EventModule\BasePresenter as EventBasePresenter;
use Fykosak\Utils\UI\Title;

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
    protected function isEnabled(): bool
    {
        return in_array($this->getEvent()->event_type_id, self::GAME_EVENTS);
    }

    protected function getNavRoots(): array
    {
        return [
            [
                'title' => new Title(null, _('Game')),
                'items' => [
                    'Game:Submit:create' => [],
                    'Game:Submit:list' => [],
                    'Game:Close:list' => [],
                    'Game:Diplomas:default' => [],
                    'Game:Diplomas:results' => [],
                    'Game:Task:list' => [],
                    'Game:GameSetup:default' => [],
                    'Game:Seating:list' => [],
                    'Game:Presentation:default' => [],
                ],
            ],
            [
                'title' => new Title(null, _('Graphs & Statistics')),
                'items' => [
                    'Game:Statistics:task' => [],
                    'Game:Statistics:team' => [],
                    'Game:Statistics:correlation' => [],
                    'Game:Statistics:table' => [],
                ],
            ],
        ];
    }
}

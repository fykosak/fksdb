<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;

abstract class PreviewComponent extends BaseComponent
{
    protected TeamModel2 $team;
    protected TeamService2 $teamService;
    protected Handler $handler;

    public function __construct(Container $container, TeamModel2 $team)
    {
        parent::__construct($container);
        $this->handler = new Handler($container);
        $this->team = $team;
    }

    final public function injectServiceFyziklaniTask(TeamService2 $teamService): void
    {
        $this->teamService = $teamService;
    }

    final public function handleClose(): void
    {
        $sum = $this->handler->close($this->team);

        $this->getPresenter()->flashMessage(
            \sprintf(_('Team "%s" has successfully closed submitting, with total %d points.'), $this->team->name, $sum),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list', ['id' => null]);
    }
}

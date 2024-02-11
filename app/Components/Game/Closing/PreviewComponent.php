<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Game\GameException;
use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Nette\DI\Container;

final class PreviewComponent extends BaseComponent
{
    protected TeamModel2 $team;
    protected Handler $handler;

    public function __construct(Container $container, TeamModel2 $team)
    {
        parent::__construct($container);
        $this->handler = new Handler($container);
        $this->team = $team;
    }

    final public function handleClose(): void
    {
        $logger = new MemoryLogger();
        $this->handler->close($logger, $this->team, false);
        FlashMessageDump::dump($logger, $this->getPresenter());
        $this->getPresenter()->redirect('list', ['id' => null]);
    }

    /**
     * @throws CannotAccessModelException
     */
    protected function createComponentTeamSubmitsGrid(): TeamSubmitsGrid
    {
        return new TeamSubmitsGrid($this->team, $this->getContext());
    }

    /**
     * @throws NotSetGameParametersException
     */
    public function render(): void
    {
        try {
            $this->team->canClose();
            $canClose = true;
        } catch (GameException $exception) {
            $canClose = false;
        }
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'preview.latte', [
            'event' => $this->team->event,
            'canClose' => $canClose,
            'task' => $this->handler->getNextTask($this->team),
        ]);
    }
}

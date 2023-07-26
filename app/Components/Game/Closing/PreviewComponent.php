<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Game\GameException;
use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\FlashMessageDump;
use Nette\DI\Container;

class PreviewComponent extends BaseComponent
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
        $this->handler->close($this->team, false);
        FlashMessageDump::dump($this->handler->logger, $this->getPresenter());
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
        $this->template->event = $this->team->event;
        $this->template->canClose = false;
        try {
            $this->team->canClose();
            $this->template->canClose = true;
        } catch (GameException $exception) {
            $this->template->canClose = false;
        }
        $this->template->task = $this->handler->getNextTask($this->team);
        /** @phpstan-ignore-next-line */
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'preview.latte');
    }
}

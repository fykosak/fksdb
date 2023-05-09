<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
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
     * @throws NotSetGameParametersException
     */
    public function render(): void
    {
        $this->template->event = $this->team->event;
        $this->template->task = $this->handler->getNextTask($this->team);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'fof.latte');
    }
}

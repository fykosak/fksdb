<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;

class FOFPreviewComponent extends PreviewComponent
{
    /**
     * @throws NotSetGameParametersException
     */
    public function render(): void
    {
        $this->template->task = $this->getNextTask();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'fof.latte');
    }


    /**
     * @throws NotSetGameParametersException
     */
    private function getNextTask(): string
    {
        $submits = $this->team->getNonRevokedSubmits()->count('*');
        $tasksOnBoard = $this->team->event->getGameSetup()->tasks_on_board;
        /** @var TaskModel|null $nextTask */
        $nextTask = $this->team->event
            ->getTasks()
            ->order('label')
            ->limit(1, $submits + $tasksOnBoard)
            ->fetch();
        return ($nextTask) ? $nextTask->label : '';
    }
}

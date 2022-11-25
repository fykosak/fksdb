<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Fyziklani\Closing;

use FKSDB\Models\Fyziklani\NotSetGameParametersException;
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
        $tasksOnBoard = $this->team->event->getFyziklaniGameSetup()->tasks_on_board;
        /** @var TaskModel|null $nextTask */
        $nextTask = $this->team->event
            ->getFyziklaniTasks()
            ->order('label')
            ->limit(1, $submits + $tasksOnBoard)
            ->fetch();
        return ($nextTask) ? $nextTask->label : '';
    }
}

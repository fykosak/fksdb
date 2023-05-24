<?php

declare(strict_types=1);

namespace FKSDB\Models\Submits;

use FKSDB\Models\ORM\Models\TaskModel;
use Nette\Http\IResponse;
use Nette\InvalidStateException;

class TaskNotQuizException extends InvalidStateException
{
    public function __construct(TaskModel $task, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                _('Task %s is not a quiz.'),
                $task->task_id,
            ),
            IResponse::S404_NotFound,
            $previous
        );
    }
}

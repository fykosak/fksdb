<?php

declare(strict_types=1);

namespace FKSDB\Models\Submits;

use FKSDB\Models\ORM\Models\SubmitModel;
use Nette\Http\IResponse;
use Nette\InvalidStateException;

class SubmitNotQuizException extends InvalidStateException
{
    public function __construct(SubmitModel $submit, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                _('Submit %s of task %s was not submitted by quiz form.'),
                $submit->submit_id,
                $submit->task,
            ),
            IResponse::S404_NotFound,
            $previous
        );
    }
}

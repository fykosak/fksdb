<?php

declare(strict_types=1);

namespace FKSDB\Models\Submits;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitQuestionModel;
use FKSDB\Models\ORM\Models\SubmitQuestionAnswerModel;
use FKSDB\Models\ORM\Services\SubmitQuestionAnswerService;
use Nette\Application\ForbiddenRequestException;
use Nette\Utils\DateTime;

class QuizHandler
{

    private SubmitQuestionAnswerService $submitQuestionAnswerService;

    public function __construct(SubmitQuestionAnswerService $service)
    {
        $this->submitQuestionAnswerService = $service;
    }

    public function saveQuestionAnswer(
        ?string $answer,
        SubmitQuestionModel $question,
        ContestantModel $contestant
    ): SubmitQuestionAnswerModel {

        // check if task is opened for submitting
        if (
            ($question->task->submit_start && $question->task->submit_start->getTimestamp() > time()) ||
            ($question->task->submit_deadline && $question->task->submit_deadline->getTimestamp() < time())
        ) {
            throw new ForbiddenRequestException(
                sprintf(_('Task %s is not opened for submitting.'), $question->task->task_id)
            );
        }

        /** @var SubmitQuestionAnswerModel $answer */
        $submit = $contestant->getAnswer($question);

        // create new submit if none exists
        if (!isset($submit)) {
            return $this->submitQuestionAnswerService->storeModel([
                'submit_question_id' => $question->submit_question_id,
                'contestant_id' => $contestant->contestant_id,
                'submitted_on' => new DateTime(),
                'answer' => $answer,
            ]);
        }

        // if the answer is the same, ignore
        if ($submit->answer == $answer) {
            return $submit;
        }

        // save new answer
        return $this->submitQuestionAnswerService->storeModel([
            'submitted_on' => new DateTime(),
            'answer' => $answer,
        ], $submit);
    }
}

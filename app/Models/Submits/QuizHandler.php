<?php

declare(strict_types=1);

namespace FKSDB\Models\Submits;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\SubmitQuestionModel;
use FKSDB\Models\ORM\Models\SubmitQuestionAnswerModel;
use FKSDB\Models\ORM\Models\SubmitSource;
use FKSDB\Models\ORM\Services\SubmitQuestionAnswerService;
use FKSDB\Models\ORM\Services\SubmitService;
use Nette\Application\ForbiddenRequestException;
use Nette\Utils\DateTime;

class QuizHandler
{

    private SubmitQuestionAnswerService $submitQuestionAnswerService;
    private SubmitService $submitService;

    public function __construct(SubmitQuestionAnswerService $answerService, submitService $submitService)
    {
        $this->submitQuestionAnswerService = $answerService;
        $this->submitService = $submitService;
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

        // create task submit
        /** @var SubmitQuestionAnswerModel $answer */
        $answerModel = $contestant->getAnswer($question);

        // answer exists and the answer is the same -> everything is ok so dont update
        if (isset($answerModel) && $answerModel->answer === $answer) {
            return $answerModel;
        }

        if (isset($answerModel)) {
            // save answer
            $answerModel = $this->submitQuestionAnswerService->storeModel([
                'submitted_on' => new DateTime(),
                'answer' => $answer,
            ], $answerModel);
        } else {
            // create new answer
            $answerModel = $this->submitQuestionAnswerService->storeModel([
                'submit_question_id' => $question->submit_question_id,
                'contestant_id' => $contestant->contestant_id,
                'submitted_on' => new DateTime(),
                'answer' => $answer,
            ]);
        }

        /** @var SubmitModel $submitModel */
        $submitModel = $contestant->getSubmitForTask($question->task);

        // save submit
        // TODO dont save everything
        // TODO dont update on every changed answer
        $this->submitService->storeModel([
            'submitted_on' => new DateTime(),
            'source' => SubmitSource::QUIZ,
            'task_id' => $question->task->task_id,
            'contestant_id' => $contestant->contestant_id,
        ], $submitModel);

        return $answerModel;
    }
}
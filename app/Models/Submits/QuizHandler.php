<?php

declare(strict_types=1);

namespace FKSDB\Models\Submits;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\SubmitQuestionModel;
use FKSDB\Models\ORM\Models\SubmitQuestionAnswerModel;
use FKSDB\Models\ORM\Services\SubmitQuestionAnswerService;
use Nette\Utils\DateTime;

class QuizHandler {

    private SubmitQuestionAnswerService $submitQuestionAnswerService;

    function __construct(SubmitQuestionAnswerService $service) {
        $this->submitQuestionAnswerService = $service;
    }

    function saveQuestionAnswer(?string $answer, SubmitQuestionModel $question, ContestantModel $contestant): SubmitQuestionAnswerModel
    {
        /** @var SubmitQuestionAnswerModel $answer */
        $submit = $contestant->getAnswer($question);
        if ($submit) {
            return $this->submitQuestionAnswerService->storeModel([
                'submitted_on' => new DateTime(),
                'answer' => $answer,
            ], $submit);
        }

        return $this->submitQuestionAnswerService->storeModel([
            'submit_question_id' => $question->submit_question_id,
            'contestant_id' => $contestant->contestant_id,
            'submitted_on' => new DateTime(),
            'answer' => $answer,
        ]);
    }

}

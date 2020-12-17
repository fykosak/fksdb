<?php

namespace FKSDB\Model\ORM\Services;

use FKSDB\ORM\DeprecatedLazyService;
use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\ModelContestant;
use FKSDB\Model\ORM\Models\ModelQuizQuestion;
use FKSDB\Model\ORM\Models\ModelSubmitQuizQuestion;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Nette\Utils\DateTime;

/**
 * @author Miroslav Jarý <mira.jary@gmail.com>
 */
class ServiceSubmitQuizQuestion extends AbstractServiceSingle {
    use DeprecatedLazyService;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_SUBMIT_QUIZ, ModelSubmitQuizQuestion::class);
    }

    public function findByContestant(int $ctId, int $questionId): ?ModelSubmitQuizQuestion {
        /** @var ModelSubmitQuizQuestion $result */
        $result = $this->getTable()->where([
            'ct_id' => $ctId,
            'question_id' => $questionId,
        ])->fetch();
        return $result ?: null;
    }

    public function saveSubmittedQuestion(ModelQuizQuestion $question, ModelContestant $contestant, ?string $answer): void {
        $submit = $this->findByContestant($contestant->ct_id, $question->question_id);
        if ($submit) {
            $this->updateModel2($submit, [
                'submitted_on' => new DateTime(),
                'answer' => $answer,
            ]);
        } else {
            $this->createNewModel([
                'question_id' => $question->question_id,
                'ct_id' => $contestant->ct_id,
                'submitted_on' => new DateTime(),
                'answer' => $answer,
            ]);
        }
    }
}

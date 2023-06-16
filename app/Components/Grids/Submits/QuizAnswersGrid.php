<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Submits;

use FKSDB\Components\Grids\Components\Grid;
use FKSDB\Components\Grids\Components\Renderer\RendererBaseItem;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\SubmitQuestionAnswerModel;
use FKSDB\Models\ORM\Models\SubmitQuestionModel;
use FKSDB\Models\Submits\SubmitNotQuizException;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Utils\Html;

class QuizAnswersGrid extends Grid
{

    private SubmitModel $submit;
    private bool $showResults;

    /**
     * @throws SubmitNotQuizException
     */
    public function __construct(Container $container, SubmitModel $submit, bool $showResults)
    {
        if (!$submit->isQuiz()) {
            throw new SubmitNotQuizException($this->submit);
        }
        $this->submit = $submit;
        $this->showResults = $showResults;
        parent::__construct($container);
    }

    protected function getModels(): TypedGroupedSelection
    {
        return $this->submit->task->getQuestions()->order('label');
    }

    protected function configure(): void
    {
        /**
         * @var SubmitQuestionAnswerModel $answer
         */
        $this->addColumn(
            new RendererBaseItem(
                $this->container,
                fn(SubmitQuestionModel $question): string => $question->getFQName(),
                new Title(null, _('Name'))
            ),
            'name'
        );

        $this->addColumn(
            new RendererBaseItem(
                $this->container,
                fn(SubmitQuestionModel $question): int => $question->points ?? 0,
                new Title(null, _('Points'))
            ),
            'points'
        );

        $this->addColumn(
            new RendererBaseItem(
                $this->container,
                function (SubmitQuestionModel $question): Html {
                    $answer = $this->submit->contestant->getAnswer($question);
                    if (isset($answer)) {
                        return Html::el('span')->setText($answer->answer);
                    }
                    return Html::el('i')->setAttribute('class', 'text-warning fas fa-slash fa-flip-horizontal');
                },
                new Title(null, _('Answer'))
            ),
            'answer'
        );

        if (!$this->showResults) {
            return;
        }

        $this->addColumn(
            new RendererBaseItem(
                $this->container,
                fn(SubmitQuestionModel $question): string => $question->answer,
                new Title(null, _('Correct answer'))
            ),
            'correct_answer'
        );

        $this->addColumn(
            new RendererBaseItem(
                $this->container,
                function (SubmitQuestionModel $question): Html {
                    $answer = $this->submit->contestant->getAnswer($question);
                    if (!isset($answer)) {
                        return Html::el('i')->setAttribute('class', 'text-warning fas fa-slash fa-flip-horizontal');
                    }

                    if ($answer->answer === $question->answer) {
                        return Html::el('i')->setAttribute('class', 'text-success fa fa-check');
                    }

                    return Html::el('i')->setAttribute('class', 'text-danger fas fa-times');
                },
                new Title(null, _('Correctness'))
            ),
            'correctness'
        );
    }
}

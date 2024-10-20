<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Submits;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\SubmitQuestionModel;
use FKSDB\Models\Submits\SubmitNotQuizException;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Utils\Html;

/**
 * @phpstan-extends BaseGrid<SubmitQuestionModel,array{}>
 */
class QuizAnswersGrid extends BaseGrid
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

    /**
     * @phpstan-return TypedGroupedSelection<SubmitQuestionModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->submit->task->getQuestions()->order('label');
    }

    protected function configure(): void
    {
        $this->addTableColumn(
            new RendererItem(
                $this->container,
                fn(SubmitQuestionModel $question): string => $question->getFQName(),
                new Title(null, _('Task'))
            ),
            'name'
        );

        $this->addTableColumn(
            new RendererItem(
                $this->container,
                fn(SubmitQuestionModel $question): string => (string)($question->points ?? 0),
                new Title(null, _('Points'))
            ),
            'points'
        );

        $this->addTableColumn(
            new RendererItem(
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

        $this->addTableColumn(
            new RendererItem(
                $this->container,
                fn(SubmitQuestionModel $question): string => $question->answer,
                new Title(null, _('Correct answer'))
            ),
            'correct_answer'
        );

        $this->addTableColumn(
            new RendererItem(
                $this->container,
                function (SubmitQuestionModel $question): Html {
                    $answer = $this->submit->contestant->getAnswer($question);
                    if (!isset($answer)) {
                        return Html::el('i')->setAttribute('class', 'text-warning fas fa-slash fa-flip-horizontal');
                    }

                    if ($answer->answer === $question->answer) {
                        return Html::el('i')->setAttribute('class', 'text-success fas fa-check');
                    }

                    return Html::el('i')->setAttribute('class', 'text-danger fas fa-times');
                },
                new Title(null, _('Correctness'))
            ),
            'correctness'
        );
    }
}

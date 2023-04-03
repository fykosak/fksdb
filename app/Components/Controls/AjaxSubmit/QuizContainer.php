<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\AjaxSubmit;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\TaskModel;
use Nette\DI\Container;

class QuizContainer extends ContainerWithOptions
{
    private TaskModel $task;

    public function __construct(Container $container, TaskModel $task)
    {
        parent::__construct($container);
        $this->task = $task;
        $this->configure();
    }

    public function configure(): void
    {
        $items = [
            'A'=>'A',
            'B'=>'B',
            'C'=>'C',
            'D'=>'D',
        ];

        $questions = $this->task->getQuestions()->order('label');

        /** @var SubmitQuestionModel $question */
        foreach($questions as $question) {
            $this->addRadioList('question'.$question->submit_question_id, $question->getFQName(), $items);
        }
    }
}

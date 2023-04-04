<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\AjaxSubmit\Quiz;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\TaskModel;
use Nette\DI\Container;

class QuizContainer extends ContainerWithOptions
{
    private TaskModel $task;
    private ?ContestantModel $contestant;

    public function __construct(Container $container, TaskModel $task, ?ContestantModel $contestant)
    {
        parent::__construct($container);
        $this->task = $task;
        $this->contestant = $contestant;
        $this->configure();
    }

    public function configure(): void
    {
        $questions = $this->task->getQuestions()->order('label');
        /** @var SubmitQuestionModel $question */
        foreach($questions as $question) {
            $answer = isset($this->contestant) ? $this->contestant->getAnswer($question) : null;
            $questionContainer = new QuizQuestionContainer($this->container, $question, $answer);
            $this->addComponent($questionContainer, 'question'.$question->submit_question_id);
        }
    }
}

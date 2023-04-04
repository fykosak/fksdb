<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\AjaxSubmit\Quiz;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\SubmitQuestionAnswerModel;
use FKSDB\Models\ORM\Models\SubmitQuestionModel;
use Nette\DI\Container;

class QuizQuestionContainer extends ContainerWithOptions
{
    private SubmitQuestionModel $question;
    private ?SubmitQuestionAnswerModel $answer;

    public function __construct(Container $container, SubmitQuestionModel $question, ?SubmitQuestionAnswerModel $answer)
    {
        parent::__construct($container);
        $this->question = $question;
        $this->answer = $answer;
        $this->configure();
    }

    public function configure(): void
    {
        $items = [
            'A' => 'A',
            'B' => 'B',
            'C' => 'C',
            'D' => 'D',
        ];

        $this->setOption('label', $this->question->getFQName());
        $select = $this->addRadioList('option', '', $items);

        if (isset($this->answer)) {
            $select->setDefaultValue($this->answer->answer);
        }
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\AjaxSubmit\Quiz;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Components\EntityForms\ReferencedPersonTrait;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Services\ContestantService;
use FKSDB\Models\Submits\QuizHandler;
use FKSDB\Models\Persons\Resolvers\SelfResolver;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\User;

class QuizComponent extends FormComponent
{
    use ReferencedPersonTrait;

    public const CONT_CONTESTANT = 'contestant';

    private TaskModel $task;
    private ?ContestantModel $contestant;
    private User $user;
    private ContestantService $contestantService;
    private QuizHandler $handler;

    public function __construct(Container $container, TaskModel $task, ContestantModel $contestant = null)
    {
        parent::__construct($container);
        $this->task = $task;
        $this->contestant = $contestant;
    }

    final public function inject(
        User $user,
        ContestantService $contestantService,
        QuizHandler $handler
    ) {
        $this->user = $user;
        $this->contestantService = $contestantService;
        $this->handler = $handler;
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('Submit a quiz'));
    }

    protected function configureForm(Form $form): void
    {
        $quizQuestions = new QuizContainer($this->container, $this->task, $this->contestant);
        $quizQuestions->setOption('label', $this->task->getFQName());
        $form->addComponent($quizQuestions, 'quiz_questions');

        // show contestant registration form if contestant is null
        if (!isset($this->contestant)) {
            $container = new ContainerWithOptions($this->container);
            $referencedId = $this->referencedPersonFactory->createReferencedPerson(
                $this->getContext()->getParameters()['forms']['registerContestant' .
                ucfirst($this->task->getContestYear()->contest->getContestSymbol())],
                $this->task->getContestYear(),
                PersonSearchContainer::SEARCH_EMAIL,
                false,
                new SelfResolver($this->user)
            );
            $referencedId->searchContainer->setOption('label', _('Contestant'));
            $referencedId->referencedContainer->setOption('label', _('Contestant'));
            $container->addComponent($referencedId, 'person_id');
            $form->addComponent($container, self::CONT_CONTESTANT);
        }
    }

    protected function handleSuccess(SubmitButton $button): void
    {
        $form = $button->getForm();
        $values = $form->getValues();
        try {
            // create and save contestant
            if (!isset($this->contestant)) {
                /** @var ReferencedId $referencedId */
                $referencedId = $form[self::CONT_CONTESTANT]['person_id'];
                /** @var PersonModel $person */
                $person = $referencedId->getModel();
                /** @var ContestModel $contestYear */
                $contestant = $person->getContestantByContestYear($this->task->getContestYear());
                // if person is not a contestant in the contest year, create him
                $this->contestant = $contestant ?? $this->contestantService->storeModel([
                    'contest_id' => $this->task->getContestYear()->contest_id,
                    'person_id' => $person->person_id,
                    'year' => $this->task->getContestYear()->year,
                ], $contestant);
            }

            // TODO define and retrive name of question field in the same place

            // create quiz submit
            $this->handler->storeSubmit($this->task, $this->contestant);
            // create submit for each quiz question
            foreach ($this->task->getQuestions() as $question) {
                $answer = $values['quiz_questions']['question' . $question->submit_question_id]['option'];
                if (isset($answer)) {
                    $this->handler->storeQuestionAnswer($answer, $question, $this->contestant);
                }
            }

            $this->flashMessage(_('Submitted'), Message::LVL_SUCCESS);
            $this->getPresenter()->redirect('this');
        } catch (ModelException $exception) {
            $this->flashMessage(_('Error'), Message::LVL_ERROR);
        }
    }
}

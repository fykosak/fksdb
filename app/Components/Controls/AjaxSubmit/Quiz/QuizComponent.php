<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\AjaxSubmit\Quiz;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Components\EntityForms\ReferencedPersonTrait;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\CaptchaBox;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\SubmitQuestionModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\Persons\Resolvers\SelfResolver;
use FKSDB\Models\Results\ResultsModelFactory;
use FKSDB\Models\Submits\QuizHandler;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\Security\User;

class QuizComponent extends FormComponent
{
    use ReferencedPersonTrait;

    public const CONT_CONTESTANT = 'contestant';

    private TaskModel $task;
    private ?ContestantModel $contestant;
    private User $user;
    private QuizHandler $handler;
    private AccountManager $accountManager;
    private string $lang;

    public function __construct(Container $container, string $lang, TaskModel $task, ContestantModel $contestant = null)
    {
        parent::__construct($container);
        $this->lang = $lang;
        $this->task = $task;
        $this->contestant = $contestant;
    }

    final public function inject(
        User $user,
        QuizHandler $handler,
        AccountManager $accountManager
    ) {
        $this->user = $user;
        $this->handler = $handler;
        $this->accountManager = $accountManager;
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('save', _('Save'));
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

            $form->addComponent(new CaptchaBox(), 'captcha');
        }
    }

    /**
     * @throws BadTypeException
     * @throws BadRequestException
     *
     */
    protected function handleSuccess(SubmitButton $button): void
    {
        $form = $button->getForm();
        $values = $form->getValues();
        try {
            // create and save contestant
            // TODO separate contestant creation into handler and reuse it in contestant register form
            if (!isset($this->contestant)) {
                /** @var ReferencedId $referencedId */
                $referencedId = $form[self::CONT_CONTESTANT]['person_id'];
                /** @var PersonModel $person */
                $person = $referencedId->getModel();
                $contestant = $person->getContestantByContestYear($this->task->getContestYear());
                // if person is not a contestant in the contest year, create him
                $strategy = ResultsModelFactory::findEvaluationStrategy(
                    $this->getContext(),
                    $this->task->getContestYear()
                );
                $this->contestant = $contestant
                    ? $strategy->updateCategory($contestant)
                    : $strategy->createContestant($person);

                // send invite mail if the person does not have a login
                $email = $person->getInfo()->email;
                if ($email && !$person->getLogin()) {
                    $this->accountManager->sendLoginWithInvitation($person, $email, $this->lang);
                }
            }

            // TODO define and retrive name of question field in the same place
            // create quiz submit
            $this->handler->storeSubmit($this->task, $this->contestant);
            // create submit for each quiz question
            /** @var SubmitQuestionModel $question */
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
        } catch (InvalidArgumentException $exception) {
            $this->flashMessage($exception->getMessage());
        }
    }
}

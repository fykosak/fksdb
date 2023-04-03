<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\AjaxSubmit;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Components\EntityForms\ReferencedPersonTrait;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\Persons\Resolvers\SelfResolver;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\User;

class QuizComponent extends FormComponent {

    public const CONT_CONTESTANT = 'contestant';

    use ReferencedPersonTrait;

    private TaskModel $task;
    private User $user;

    public function __construct(Container $container, TaskModel $task)
    {
        parent::__construct($container);
        $this->task = $task;
    }

    final public function inject(User $user) {
        $this->user = $user;
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('Save'));
    }

    protected function handleSuccess(SubmitButton $button): void
    {
        $form = $button->getForm();
        $values = $form->getValues();
        try {
            $this->flashMessage(_('Submitted'), Message::LVL_SUCCESS);
            Debugger::log($values);
            $this->getPresenter()->redirect('this');
        } catch (ModelException $exception) {
            $this->flashMessage(_('Error'), Message::LVL_ERROR);
        }
    }

    protected function configureForm(Form $form): void
    {
        $container = new ContainerWithOptions($this->container);

        $quizQuestions = new QuizContainer($this->container, $this->task);
        $quizQuestions->setOption('label', _('Quiz'));
        $container->addComponent($quizQuestions, 'quiz_questions');

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

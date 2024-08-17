<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Components\Game\Submits\ClosedSubmittingException;
use FKSDB\Components\Game\Submits\Handler\Handler;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-extends ModelForm<SubmitModel,array{points:int}>
 */
class FyziklaniSubmitFormComponent extends ModelForm
{
    private Handler $handler;

    public function __construct(Container $container, ?Model $model)
    {
        parent::__construct($container, $model);
        $this->handler = $this->model->fyziklani_team->event->createGameHandler($this->getContext());
    }

    /**
     * @throws NotSetGameParametersException
     */
    protected function configureForm(Form $form): void
    {
        $form->addComponent($this->createPointsField(), 'points');
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults([
                'team_id' => $this->model->fyziklani_team_id,
                'points' => $this->model->points,
            ]);
        }
    }

    protected function onException(\Throwable $exception): bool
    {
        if ($exception instanceof ClosedSubmittingException) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            return true;
        }
        return parent::onException($exception);
    }

    /**
     * TODO to table-reflection factory
     * @throws NotSetGameParametersException
     */
    private function createPointsField(): RadioList
    {
        $field = new RadioList(_('Number of points'));
        $items = [];
        foreach ($this->model->fyziklani_team->event->getGameSetup()->getAvailablePoints() as $points) {
            $items[$points] = $points;
        }
        $field->setItems($items);
        $field->setRequired();
        return $field;
    }

    /**
     * @throws InvalidLinkException
     */
    protected function innerSuccess(array $values, Form $form): Model
    {
        $this->handler->edit($this->model, (int)$values['points']);
        return $this->model;
    }

    protected function successRedirect(Model $model): void
    {
        foreach ($this->handler->logger->getMessages() as $message) {
            // interpret html edit links
            $this->getPresenter()->flashMessage(Html::el()->setHtml($message->text), $message->level);
        }
        $this->handler->logger->clear();
        $this->redirect('this');
    }
}

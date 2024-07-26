<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Components\Game\Submits\ClosedSubmittingException;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-extends EntityFormComponent<SubmitModel>
 */
class FyziklaniSubmitFormComponent extends EntityFormComponent
{
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

    protected function handleFormSuccess(Form $form): void
    {
        /**
         * @phpstan-var array{points:int} $values
         */
        $values = $form->getValues('array');
        try {
            $handler = $this->model->fyziklani_team->event->createGameHandler($this->getContext());
            $handler->edit($this->model, (int)$values['points']);
            foreach ($handler->logger->getMessages() as $message) {
                // interpret html edit links
                $this->getPresenter()->flashMessage(Html::el()->setHtml($message->text), $message->level);
            }
            $handler->logger->clear();
            $this->redirect('this');
        } catch (ClosedSubmittingException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            $this->redirect('this');
        }
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
}

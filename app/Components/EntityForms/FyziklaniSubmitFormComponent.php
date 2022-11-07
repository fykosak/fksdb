<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\Fyziklani\Submit\ClosedSubmittingException;
use FKSDB\Models\Fyziklani\Submit\Handler;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Form;

/**
 * @property SubmitModel $model
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
        $values = $form->getValues();
        try {
            $logger = new MemoryLogger();
            $handler = new Handler($this->model->fyziklani_team->event, $this->getContext());
            $handler->changePoints($logger, $this->model, $values['points']);
            FlashMessageDump::dump($logger, $this->getPresenter());
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
        foreach ($this->model->fyziklani_team->event->getFyziklaniGameSetup()->getAvailablePoints() as $points) {
            $items[$points] = $points;
        }
        $field->setItems($items);
        $field->setRequired();
        return $field;
    }
}

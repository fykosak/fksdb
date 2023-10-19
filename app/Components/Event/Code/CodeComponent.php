<?php

declare(strict_types=1);

namespace FKSDB\Components\Event\Code;

use FKSDB\Components\Controls\FormComponent\CodeForm;
use FKSDB\Components\MachineCode\MachineCode;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;

final class CodeComponent extends CodeForm
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.latte';
    }

    /**
     * @throws BadRequestException
     * @throws NotFoundException
     */
    protected function innerHandleSuccess(Model $model): void
    {
        $application = $this->resolveApplication($model);
        if ($application->event_id !== $this->event->event_id) {
            throw new BadRequestException(_('Application belongs to another event.'));
        }
        $this->getPresenter()->redirect('detail', ['id' => $application->getPrimary()]);
    }

    protected function configureForm(Form $form): void
    {
        $form->elementPrototype->target = '_blank';
        parent::configureForm($form);
    }

    /**
     * @return TeamModel2|EventParticipantModel
     * @throws BadRequestException
     * @throws NotFoundException
     */
    private function resolveApplication(Model $model): Model
    {
        if ($model instanceof EventParticipantModel || $model instanceof TeamModel2) {
            return $model;
        } elseif ($model instanceof PersonModel) {
            return $model->getApplication($this->event);
        }
        throw new BadRequestException(_('Wrong type of code.'));
    }

    /**
     * @throws NotImplementedException
     */
    protected function getSalt(): string
    {
        return MachineCode::getSaltForEvent($this->event);
    }
}

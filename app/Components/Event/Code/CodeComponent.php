<?php

declare(strict_types=1);

namespace FKSDB\Components\Event\Code;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Components\MachineCode\MachineCode;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

final class CodeComponent extends FormComponent
{
    private EventModel $event;

    public ?string $action = null;

    public function __construct(
        Container $container,
        EventModel $event
    ) {
        parent::__construct($container);
        $this->event = $event;
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.latte';
    }

    protected function handleSuccess(Form $form): void
    {
        /** @phpstan-var array{code:string} $values */
        $values = $form->getValues('array');
        try {
            $application = $this->resolveApplication(
                MachineCode::parseHash(
                    $this->container,
                    $values['code'],
                    MachineCode::getSaltForEvent($this->event)
                )
            );
            if ($application->event_id !== $this->event->event_id) {
                throw new BadRequestException(_('Application belongs to another event.'));
            }
            $this->getPresenter()->redirect('detail', ['id' => $application->getPrimary()]);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->getPresenter()->flashMessage(_('Error: ') . $exception->getMessage(), Message::LVL_ERROR);
        }
        $this->getPresenter()->redirect('this');
    }

    /**
     * @return TeamModel2|EventParticipantModel
     * @throws BadRequestException
     * @throws NotFoundException
     */
    private function resolveApplication(Model $model): Model
    {
        if ($model instanceof EventParticipantModel) {
            return $model;
        } elseif ($model instanceof TeamModel2) {
            return $model;
        } elseif ($model instanceof PersonModel) {
            return $model->getApplication($this->event);
        }
        throw new BadRequestException(_('Wrong type of code.'));
    }

    protected function configureForm(Form $form): void
    {
        $form->elementPrototype->target = '_blank';
        $form->addText('code', _('Code'));
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('Do!'));
    }
}

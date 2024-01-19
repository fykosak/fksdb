<?php

declare(strict_types=1);

namespace FKSDB\Components\Event\Code;

use FKSDB\Components\Controls\FormComponent\CodeForm;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\MachineCode\MachineCodeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-import-type TSupportedModel from MachineCode
 */
final class CodeRedirectComponent extends CodeForm
{
    private EventModel $event;
    /** @persistent */
    private ?Model $model = null;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte', [
            'model' => $this->model,
        ]);
    }

    /**
     * @phpstan-param TSupportedModel $model
     * @return TeamModel2|EventParticipantModel
     * @throws BadRequestException
     * @throws NotFoundException
     */
    private function resolveApplication(Model $model): Model
    {
        if ($this->event->isTeamEvent() && $model instanceof TeamModel2) {
            return $model;
        } elseif (!$this->event->isTeamEvent() && $model instanceof PersonModel) {
            return $model->getEventParticipant($this->event);
        }
        throw new BadRequestException(_('Wrong type of code.'));
    }

    /**
     * @phpstan-param TSupportedModel $model
     */
    protected function innerHandleSuccess(Model $model, Form $form): void
    {
        try {
            $this->model = $this->resolveApplication($model);
            if ($this->model->event_id !== $this->event->event_id) {
                throw new BadRequestException(_('Application belongs to another event.'));
            }
            $form->reset();
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            $this->getPresenter()->redirect('this');
        }
    }

    /**
     * @throws MachineCodeException
     */
    protected function getSalt(): string
    {
        return $this->event->getSalt();
    }
}

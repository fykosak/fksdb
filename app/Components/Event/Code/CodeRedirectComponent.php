<?php

declare(strict_types=1);

namespace FKSDB\Components\Event\Code;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Controls\Button;
use Nette\Forms\Form;

final class CodeRedirectComponent extends BaseComponent
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }

    protected function createComponentFormControl(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->elementPrototype->target = '_blank';
        $form->addText('code', _('Code'))->setRequired(true);
        $form->addSubmit('detail', _('button.detail'))->onClick[] =
            fn(Button $button) => $this->handleClick($form, 'detail');
        $form->addSubmit('edit', _('button.edit'))->onClick[] =
            fn(Button $button) => $this->handleClick($form, 'edit');
        return $control;
    }

    private function handleClick(Form $form, string $action): void
    {
        /** @phpstan-var array{code:string} $values */
        $values = $form->getValues('array');
        try {
            $model = MachineCode::parseHash(
                $this->container,
                $values['code'],
                $this->event->getSalt()
            );
            $application = $this->resolveApplication($model);
            if ($application->event_id !== $this->event->event_id) {
                throw new BadRequestException(_('Application belongs to another event.'));
            }
            $this->getPresenter()->redirect($action, ['id' => $application->getPrimary()]);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            $this->getPresenter()->redirect('this');
        }
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
}

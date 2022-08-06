<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventOrgModel;
use FKSDB\Models\ORM\Services\EventOrgService;
use FKSDB\Models\Utils\FormUtils;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @property EventOrgModel|null $model
 */
class EventOrgFormComponent extends EntityFormComponent
{
    use ReferencedPersonTrait;

    public const CONTAINER = 'event_org';

    private EventOrgService $eventOrgService;
    private EventModel $event;

    public function __construct(Container $container, EventModel $event, ?EventOrgModel $model)
    {
        parent::__construct($container, $model);
        $this->event = $event;
    }

    final public function injectPrimary(EventOrgService $eventOrgService): void
    {
        $this->eventOrgService = $eventOrgService;
    }

    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer();
        $personInput = $this->createPersonSelect();
        $personInput->setDisabled(isset($this->model));
        $container->addComponent($personInput, 'person_id');
        $container->addText('note', _('Note'));
        $form->addComponent($container, self::CONTAINER);
    }

    protected function handleFormSuccess(Form $form): void
    {
        $data = FormUtils::emptyStrToNull2($form->getValues()[self::CONTAINER]);
        if (!isset($data['event_id'])) {
            $data['event_id'] = $this->event->event_id;
        }
        $this->eventOrgService->storeModel($data, $this->model);
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Event org has been updated') : _('Event org has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }

    /**
     * @throws BadTypeException
     */
    protected function setDefaults(): void
    {
        if (isset($this->model)) {
            $this->getForm()->setDefaults([self::CONTAINER => $this->model->toArray()]);
        }
    }
}

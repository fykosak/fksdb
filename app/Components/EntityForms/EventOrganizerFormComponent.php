<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\Authorization\Authorizators\Authorizator;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use FKSDB\Models\ORM\Services\EventOrganizerService;
use FKSDB\Models\Persons\Resolvers\AclResolver;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<EventOrganizerModel,array{container:array{person_id:int,note:string}}>
 */
class EventOrganizerFormComponent extends ModelForm
{
    use ReferencedPersonTrait;

    public const CONTAINER = 'container';

    private EventOrganizerService $service;
    private Authorizator $authorizator;
    private EventModel $event;

    public function __construct(Container $container, EventModel $event, ?EventOrganizerModel $model)
    {
        parent::__construct($container, $model);
        $this->event = $event;
    }

    final public function injectPrimary(
        EventOrganizerService $service,
        Authorizator $authorizator
    ): void {
        $this->service = $service;
        $this->authorizator = $authorizator;
    }

    protected function configureForm(Form $form): void
    {
        $container = new ContainerWithOptions($this->container);
        $referencedId = $this->createPersonId(
            $this->event->getContestYear(),
            !isset($this->model),
            new AclResolver($this->authorizator, $this->event->getContestYear()->contest),
            $this->getContext()->getParameters()['forms']['adminEventOrganizer']
        );
        $container->addComponent($referencedId, 'person_id');
        $container->addText('note', _('Note'));
        $form->addComponent($container, self::CONTAINER);
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults([self::CONTAINER => $this->model->toArray()]);
        }
    }

    protected function innerSuccess(array $values, Form $form): EventOrganizerModel
    {
        $data = $values[self::CONTAINER];
        $data['event_id'] = $this->event->event_id;
        /** @var EventOrganizerModel $organizer */
        $organizer = $this->service->storeModel($data, $this->model);
        return $organizer;
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Event organizer has been updated') : _('Event organizer has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }
}

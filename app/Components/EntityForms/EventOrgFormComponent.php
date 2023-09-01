<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventOrgModel;
use FKSDB\Models\ORM\Services\EventOrgService;
use FKSDB\Models\Persons\Resolvers\AclResolver;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends EntityFormComponent<EventOrgModel>
 */
class EventOrgFormComponent extends EntityFormComponent
{
    use ReferencedPersonTrait;

    public const CONTAINER = 'event_org';

    private EventOrgService $eventOrgService;
    private ContestAuthorizator $contestAuthorizator;
    private EventModel $event;

    public function __construct(Container $container, EventModel $event, ?EventOrgModel $model)
    {
        parent::__construct($container, $model);
        $this->event = $event;
    }

    final public function injectPrimary(
        EventOrgService $eventOrgService,
        ContestAuthorizator $contestAuthorizator
    ): void {
        $this->eventOrgService = $eventOrgService;
        $this->contestAuthorizator = $contestAuthorizator;
    }

    protected function configureForm(Form $form): void
    {
        $container = new ContainerWithOptions($this->container);
        $referencedId = $this->createPersonId(
            $this->event->getContestYear(),
            !isset($this->model),
            new AclResolver($this->contestAuthorizator, $this->event->getContestYear()->contest),
            $this->getContext()->getParameters()['forms']['adminEventOrg']
        );
        $container->addComponent($referencedId, 'person_id');
        $container->addText('note', _('Note'));
        $form->addComponent($container, self::CONTAINER);
    }

    protected function handleFormSuccess(Form $form): void
    {
        /**
         * @phpstan-var array{event_org:array{person_id:int,note:string,event_id?:int}} $values
         */
        $values = $form->getValues('array');
        $data = FormUtils::emptyStrToNull2($values[self::CONTAINER]);
        if (!isset($data['event_id'])) {
            $data['event_id'] = $this->event->event_id;
        }
        $this->eventOrgService->storeModel($data, $this->model);
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Event organizer has been updated') : _('Event organizer has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults([self::CONTAINER => $this->model->toArray()]);
        }
    }
}

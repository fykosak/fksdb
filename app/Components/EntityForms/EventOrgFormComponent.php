<?php

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Messages\Message;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventOrg;
use FKSDB\Models\ORM\Services\ServiceEventOrg;
use FKSDB\Models\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @property ModelEventOrg $model
 */
class EventOrgFormComponent extends AbstractEntityFormComponent {

    use ReferencedPersonTrait;

    public const CONTAINER = 'event_org';

    private ServiceEventOrg $serviceEventOrg;
    private ModelEvent $event;

    public function __construct(Container $container, ModelEvent $event, ?ModelEventOrg $model) {
        parent::__construct($container, $model);
        $this->event = $event;
    }

    final public function injectPrimary(ServiceEventOrg $serviceEventOrg): void {
        $this->serviceEventOrg = $serviceEventOrg;
    }

    protected function configureForm(Form $form): void {
        $container = new ModelContainer();
        $personInput = $this->createPersonSelect();
        $personInput->setDisabled(isset($this->model));
        $container->addComponent($personInput, 'person_id');
        $container->addText('note', _('Note'));
        $form->addComponent($container, self::CONTAINER);
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    protected function handleFormSuccess(Form $form): void {
        $data = FormUtils::emptyStrToNull($form->getValues()[self::CONTAINER], true);
        if (!isset($data['event_id'])) {
            $data['event_id'] = $this->event->event_id;
        }
        $this->serviceEventOrg->storeModel($data, $this->model ?? null);
        $this->getPresenter()->flashMessage(!isset($this->model) ? _('Event org has been created') : _('Event org has been updated'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @return void
     * @throws BadTypeException
     */
    protected function setDefaults(): void {
        if (isset($this->model)) {
            $this->getForm()->setDefaults([self::CONTAINER => $this->model->toArray()]);
        }
    }
}

<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventOrg;
use FKSDB\ORM\Services\ServiceEventOrg;
use FKSDB\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\Forms\Form;
use Nette\DI\Container;

/**
 * Class EventOrgFormComponent
 * @author Michal Červeňák <miso@fykos.cz>
 * @property ModelEventOrg $model
 */
class EventOrgFormComponent extends EditEntityFormComponent {

    use ReferencedPersonTrait;

    public const CONTAINER = 'event_org';

    protected ServiceEventOrg $serviceEventOrg;

    protected ModelEvent $event;

    public function __construct(Container $container, ModelEvent $event, bool $create) {
        parent::__construct($container, $create);
        $this->event = $event;
    }

    public function injectPrimary(ServiceEventOrg $serviceEventOrg): void {
        $this->serviceEventOrg = $serviceEventOrg;
    }

    protected function configureForm(Form $form): void {
        $container = new ModelContainer();
        $personInput = $this->createPersonSelect();
        $personInput->setDisabled(!$this->create);
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
        if ($this->create) {
            $this->serviceEventOrg->createNewModel($data);
        } else {
            $this->serviceEventOrg->updateModel2($this->model, $data);
        }
        $this->getPresenter()->flashMessage($this->create ? _('Event org has been created') : _('Event org has been updated'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @param AbstractModelSingle|ModelEventOrg|null $model
     * @return void
     * @throws BadTypeException
     */
    protected function setDefaults(?AbstractModelSingle $model): void {
        if (!is_null($model)) {
            $this->getForm()->setDefaults([self::CONTAINER => $model->toArray()]);
        }
    }
}

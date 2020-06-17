<?php

namespace FKSDB\Components\Controls\Entity\EventOrg;

use FKSDB\Components\Controls\Entity\AbstractEntityFormControl;
use FKSDB\Components\Controls\Entity\IEditEntityForm;
use FKSDB\Components\Controls\Entity\ReferencedPersonTrait;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\ModelException;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventOrg;
use FKSDB\ORM\Services\ServiceEventOrg;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Tracy\Debugger;

class EventOrgForm extends AbstractEntityFormControl implements IEditEntityForm {

    use ReferencedPersonTrait;

    const CONTAINER = 'event_org';
    /**
     * @var ServiceEventOrg
     */
    protected $serviceEventOrg;
    /**
     * @var ModelEvent
     */
    protected $event;
    /**
     * @var bool
     */
    protected $create;

    /**
     * @var ModelEventOrg
     */
    private $model;

    /**
     * AbstractForm constructor.
     * @param Container $container
     * @param ModelEvent $event
     * @param bool $create
     */
    public function __construct(Container $container, ModelEvent $event, bool $create) {
        parent::__construct($container);
        $this->event = $event;
        $this->create = $create;
    }

    /**
     * @param ServiceEventOrg $serviceEventOrg
     * @return void
     */
    public function injectPrimary(ServiceEventOrg $serviceEventOrg) {
        $this->serviceEventOrg = $serviceEventOrg;
    }

    /**
     * @param Form $form
     * @return void
     */
    protected function configureForm(Form $form) {
        $container = new ModelContainer();
        $personInput = $this->createPersonSelect();
        $container->addComponent($personInput, 'person_id');
        $container->addText('note', _('Note'));
        $form->addComponent($container, self::CONTAINER);
        $form->addSubmit('submit', $this->create ? _('Create') : _('Save'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleFormSuccess($form);
        };
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    private function handleFormSuccess(Form $form) {
        $data = \FormUtils::emptyStrToNull($form->getValues()[self::CONTAINER], true);
        if (!isset($data['event_id'])) {
            $data['event_id'] = $this->event->event_id;
        }
        try {
            $this->create ? $this->handleCreateSuccess($data) : $this->handleEditSuccess($data);
        } catch (ModelException $exception) {
            Debugger::log($exception);
            $this->flashMessage(_('Error'), Message::LVL_DANGER);
        }
    }

    /**
     * @param AbstractModelSingle $model
     * @return void
     * @throws BadTypeException
     */
    public function setModel(AbstractModelSingle $model) {
        $this->model = $model;
        $this->getForm()->setDefaults([self::CONTAINER => $model->toArray()]);
    }

    /**
     * @param array $data
     * @return mixed|void
     * @throws AbortException
     */
    protected function handleCreateSuccess(array $data) {
        $this->getORMService()->createNewModel($data);
        $this->getPresenter()->flashMessage(_('Event org has been created'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @param array $data
     * @return void
     * @throws AbortException
     */
    protected function handleEditSuccess(array $data) {
        $this->getORMService()->updateModel2($this->model, $data);
        $this->getPresenter()->flashMessage(_('Org has been updated'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    protected function getORMService(): ServiceEventOrg {
        return $this->serviceEventOrg;
    }
}

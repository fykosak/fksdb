<?php

namespace FKSDB\Components\Controls\Entity\EventOrg;

use FKSDB\Components\Controls\Entity\AbstractEntityFormComponent;
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
use FKSDB\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\Forms\Form;
use Nette\DI\Container;
use Tracy\Debugger;

/**
 * Class EventOrgFormComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventOrgFormComponent extends AbstractEntityFormComponent implements IEditEntityForm {

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
        parent::__construct($container, $create);
        $this->event = $event;
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
    protected function handleFormSuccess(Form $form) {
        $data = FormUtils::emptyStrToNull($form->getValues()[self::CONTAINER], true);
        if (!isset($data['event_id'])) {
            $data['event_id'] = $this->event->event_id;
        }
        try {
            if ($this->create) {
                $this->getORMService()->createNewModel($data);
            } else {
                $this->getORMService()->updateModel2($this->model, $data);
            }
            $this->getPresenter()->flashMessage($this->create ? _('Event org has been created') : _('Event org has been updated'), Message::LVL_SUCCESS);
            $this->getPresenter()->redirect('list');
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

    protected function getORMService(): ServiceEventOrg {
        return $this->serviceEventOrg;
    }
}

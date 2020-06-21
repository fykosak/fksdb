<?php

namespace FKSDB\Components\Controls\Entity\Org;

use FKSDB\Components\Controls\Entity\AbstractEntityFormControl;
use FKSDB\Components\Controls\Entity\IEditEntityForm;
use FKSDB\Components\Controls\Entity\ReferencedPersonTrait;
use FKSDB\Components\Forms\Factories\OrgFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\ModelException;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelOrg;
use FKSDB\ORM\Services\ServiceOrg;
use FKSDB\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Tracy\Debugger;

class OrgForm extends AbstractEntityFormControl implements IEditEntityForm {
    use ReferencedPersonTrait;

    const CONTAINER = 'org';
    /**
     * @var ServiceOrg
     */
    protected $serviceOrg;

    /**
     * @var OrgFactory
     */
    protected $orgFactory;
    /**
     * @var ModelContest
     */
    protected $contest;
    /** @var ModelOrg */
    private $model;

    /**
     * AbstractForm constructor.
     * @param Container $container
     * @param ModelContest $contest
     * @param bool $create
     */
    public function __construct(Container $container, ModelContest $contest, bool $create) {
        parent::__construct($container, $create);
        $this->contest = $contest;
    }

    /**
     * @param OrgFactory $orgFactory
     * @param ServiceOrg $serviceOrg
     * @return void
     */
    public function injectPrimary(OrgFactory $orgFactory, ServiceOrg $serviceOrg) {
        $this->orgFactory = $orgFactory;
        $this->serviceOrg = $serviceOrg;
    }

    /**
     * @param Form $form
     * @return void
     * @throws \Exception
     */
    protected function configureForm(Form $form) {
        $container = $this->orgFactory->createOrg($this->contest);
        $personInput = $this->createPersonSelect();
        if (!$this->create) {
            $personInput->setDisabled(true);
        }
        $container->addComponent($personInput, 'person_id', 'since');
        $form->addComponent($container, self::CONTAINER);
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    protected function handleFormSuccess(Form $form) {
        $data = FormUtils::emptyStrToNull($form->getValues()[self::CONTAINER], true);
        if (!isset($data['contest_id'])) {
            $data['contest_id'] = $this->contest->contest_id;
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
     * @return void
     * @throws AbortException
     */
    protected function handleCreateSuccess(array $data) {
        $this->getORMService()->createNewModel($data);
        $this->getPresenter()->flashMessage(_('Org has been created'), Message::LVL_SUCCESS);
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

    protected function getORMService(): ServiceOrg {
        return $this->serviceOrg;
    }
}

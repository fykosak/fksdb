<?php

namespace FKSDB\Components\Controls\Entity\School;

use FKSDB\Components\Controls\Entity\IEditEntityForm;
use FKSDB\Exceptions\ModelException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelSchool;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Tracy\Debugger;

/**
 * Class EditForm
 * @package FKSDB\Components\Controls\Entity\School
 */
class EditForm extends AbstractForm implements IEditEntityForm {
    /**
     * @var ModelSchool;
     */
    private $model;

    /**
     * EditForm constructor.
     * @param Container $container
     * @throws BadRequestException
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $form = $this->getForm();
        $form->addSubmit('send', _('Save'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleEditFormSuccess($form);
        };
    }

    /**
     * @param AbstractModelSingle|ModelSchool $model
     * @throws BadRequestException
     */
    public function setModel(AbstractModelSingle $model) {
        $this->model = $model;
        $this->getForm()->setDefaults([
            self::CONT_SCHOOL => $model->toArray(),
            self::CONT_ADDRESS => $model->getAddress() ? $model->getAddress()->toArray() : null,
        ]);
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    private function handleEditFormSuccess(Form $form) {
        $connection = $this->serviceSchool->getConnection();
        $values = $form->getValues();
        $school = $this->model;
        $address = $school->getAddress();

        try {
            $connection->beginTransaction();

            /* Address */
            $data = \FormUtils::emptyStrToNull($values[self::CONT_ADDRESS]);
            $this->serviceAddress->updateModel2($address, $data);

            /* School */
            $data = \FormUtils::emptyStrToNull($values[self::CONT_SCHOOL]);
            $this->serviceSchool->updateModel2($school, $data);

            /* Finalize */
            $connection->commit();

            $this->getPresenter()->flashMessage(_('Škola upravena'), \BasePresenter::FLASH_SUCCESS);
            $this->getPresenter()->redirect('list');
        } catch (ModelException $exception) {
            $connection->rollBack();
            Debugger::log($exception, Debugger::ERROR);
            $this->getPresenter()->flashMessage(_('Chyba při úpravě školy.'), \BasePresenter::FLASH_ERROR);
        }
    }
}

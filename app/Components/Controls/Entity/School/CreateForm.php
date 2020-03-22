<?php

namespace FKSDB\Components\Controls\Entity\School;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Tracy\Debugger;

/**
 * Class CreateForm
 * @package FKSDB\Components\Controls\Entity\School
 */
class CreateForm extends AbstractForm {

    /**
     * EditForm constructor.
     * @param Container $container
     * @throws BadRequestException
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $form = $this->getForm();

        $form->addSubmit('send', _('Create'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleCreateFormSuccess($form);
        };
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    private function handleCreateFormSuccess(Form $form) {
        $connection = $this->serviceSchool->getConnection();
        $values = $form->getValues();

        try {
            if (!$connection->beginTransaction()) {
                throw new \ModelException();
            }
            Debugger::barDump($values);
            /* Address */
            $data = \FormUtils::emptyStrToNull($values[self::CONT_ADDRESS]);
            $address = $this->serviceAddress->createNewModel($data);
            /* School */
            $data = \FormUtils::emptyStrToNull($values[self::CONT_SCHOOL]);
            $data['address_id'] = $address->address_id;
            $this->serviceSchool->createNewModel($data);
            /* Finalize */
            if (!$connection->commit()) {
                throw new \ModelException();
            }

            $this->getPresenter()->flashMessage(_('Škola založena'), \BasePresenter::FLASH_SUCCESS);

            $this->getPresenter()->redirect('list'); // if there's no backlink
        } catch (\ModelException $exception) {
            Debugger::barDump($exception);
            $connection->rollBack();
            Debugger::log($exception, Debugger::ERROR);
            $this->getPresenter()->flashMessage(_('Chyba při zakládání školy.'), \BasePresenter::FLASH_ERROR);
        }
    }
}

<?php

namespace Persons;

use FKSDB\ORM\IModel;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IExtendedPersonPresenter {

    /**
     * @return IModel
     */
    public function getModel();

    /**
     * @note First '%s' is replaced with referenced person's name.
     */
    public function messageCreate();

    /**
     * @note First '%s' is replaced with referenced person's name.
     */
    public function messageEdit();

    public function messageError();

    public function messageExists();

    /**
     * @param $message
     * @param string $type
     * @return mixed
     */
    public function flashMessage($message, $type = 'info');
}


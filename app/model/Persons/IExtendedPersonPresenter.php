<?php

namespace Persons;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IExtendedPersonPresenter {

    /**
     * @return IModel|AbstractModelSingle|AbstractModelMulti
     */
    public function getModel();

    /**
     * @note First '%s' is replaced with referenced person's name.
     * @return string
     */
    public function messageCreate(): string;

    /**
     * @note First '%s' is replaced with referenced person's name.
     * @return string
     */
    public function messageEdit(): string;

    /**
     * @return string
     */
    public function messageError(): string;

    /**
     * @return string
     */
    public function messageExists(): string;

    /**
     * @param $message
     * @param string $type
     * @return void
     */
    public function flashMessage($message, $type = 'info');
}

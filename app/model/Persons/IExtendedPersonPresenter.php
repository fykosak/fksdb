<?php

namespace FKSDB\Persons;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IExtendedPersonPresenter {

    public function getModel(): ?IModel;

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

    public function messageError(): string;

    public function messageExists(): string;

    /**
     * @param string $message
     * @param string $type
     * @return void
     */
    public function flashMessage($message, $type = 'info');
}

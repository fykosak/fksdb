<?php

namespace FKSDB\Models\Persons;

use Fykosak\NetteORM\AbstractModel;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface ExtendedPersonPresenter {

    public function getModel(): ?AbstractModel;

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
     * @return \stdClass
     */
    public function flashMessage($message, string $type = 'info'): \stdClass;
}

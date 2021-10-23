<?php

namespace FKSDB\Models\Persons;

use Fykosak\NetteORM\AbstractModel;

interface ExtendedPersonPresenter {

    public function getModel(): ?AbstractModel;

    /**
     * @note First '%s' is replaced with referenced person's name.
     */
    public function messageCreate(): string;

    /**
     * @note First '%s' is replaced with referenced person's name.
     */
    public function messageEdit(): string;

    public function messageError(): string;

    public function messageExists(): string;

    /**
     * @param string $message
     */
    public function flashMessage($message, string $type = 'info'): \stdClass;
}

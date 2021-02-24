<?php

namespace FKSDB\Tests\ModelsTests\Persons;

use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Models\Persons\ExtendedPersonPresenter;

class PersonPresenter extends BasePresenter implements ExtendedPersonPresenter {

    public function getModel(): ?AbstractModelSingle {
        return null;
    }

    public function messageCreate(): string {
        return '';
    }

    public function messageEdit(): string {
        return '';
    }

    public function messageError(): string {
        return '';
    }

    public function messageExists(): string {
        return '';
    }

    public function flashMessage($message, string $type = 'info'): \stdClass {
        return new \stdClass();
    }

}

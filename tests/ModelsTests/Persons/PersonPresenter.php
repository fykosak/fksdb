<?php

namespace FKSDB\Tests\ModelsTests\Persons;

use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Models\Persons\IExtendedPersonPresenter;
use Fykosak\Utils\ORM\AbstractModel;

class PersonPresenter extends BasePresenter implements IExtendedPersonPresenter {

    public function getModel(): ?AbstractModel {
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

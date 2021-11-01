<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\Persons;

use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Models\Persons\ExtendedPersonPresenter;
use Fykosak\NetteORM\AbstractModel;
use stdClass;

class PersonPresenter extends BasePresenter implements ExtendedPersonPresenter
{

    public function getModel(): ?AbstractModel
    {
        return null;
    }

    public function messageCreate(): string
    {
        return '';
    }

    public function messageEdit(): string
    {
        return '';
    }

    public function messageError(): string
    {
        return '';
    }

    public function messageExists(): string
    {
        return '';
    }

    public function flashMessage($message, string $type = 'info'): stdClass
    {
        return new stdClass();
    }
}

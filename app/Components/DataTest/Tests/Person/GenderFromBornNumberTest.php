<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Person;

use FKSDB\Components\DataTest\Test;
use FKSDB\Components\Forms\Rules\BornNumber;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<PersonModel>
 */
class GenderFromBornNumberTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('Gender from born Id'));
    }
    public function getDescription(): ?string
    {
        return _('Tests, if gender matches born ID');
    }

    /**
     * @param PersonModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        $info = $model->getInfo();
        if (!$info || !$info->born_id) {
            return;
        }
        if (!$model->gender->value) {
            $logger->log(new Message(_('Gender is not set'), Message::LVL_WARNING));
            return;
        }
        if (BornNumber::getGender($info->born_id)->value !== $model->gender->value) {
            $logger->log(new Message(_('Gender do not match born Id'), Message::LVL_ERROR));
        }
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Person;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Tests\Test;
use FKSDB\Models\ORM\Columns\Tables\PersonInfo\BornIdColumnFactory;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<PersonModel>
 */
class GenderFromBornNumberTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('Gender from born Id'), 'fas fa-venus-mars');
    }
    public function getDescription(): ?string
    {
        return _('Tests, if gender matches born ID');
    }

    /**
     * @param PersonModel $model
     */
    public function run(TestLogger $logger, Model $model): void
    {
        try {
            $info = $model->getInfo();
            if (!$info || !$info->born_id) {
                return;
            }
            if (!$model->gender->value) {
                $logger->log(new TestMessage(_('Gender is not set'), Message::LVL_WARNING));
                return;
            }
            if (BornIdColumnFactory::getGender($info->born_id)->value !== $model->gender->value) {
                $logger->log(new TestMessage(_('Gender do not match born Id'), Message::LVL_ERROR));
            }
        } catch (\Throwable$exception) {
            $logger->log(new TestMessage($exception->getMessage(), Message::LVL_ERROR));
        }
    }

    public function getId(): string
    {
        return 'PersonGenderFromBorn';
    }
}

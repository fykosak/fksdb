<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\School;

use FKSDB\Components\DataTest\Tests\Test;
use FKSDB\Models\ORM\Models\SchoolModel;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<SchoolModel>
 */
class VerifiedSchoolTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('Verified school'));
    }

    public function getDescription(): ?string
    {
        return _('Check if school is verified.');
    }

    /**
     * @param SchoolModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        if (!$model->verified) {
            $logger->log(new Message(_('School is no verified'), Message::LVL_ERROR));
        }
    }

    public function getId(): string
    {
        return 'SchoolVerified';
    }
}

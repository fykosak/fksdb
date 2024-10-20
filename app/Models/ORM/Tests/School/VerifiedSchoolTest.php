<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\School;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Localization\LangMap;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<SchoolModel>
 */
final class VerifiedSchoolTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('Verified school'));
    }

    public function getDescription(): ?LangMap
    {
        return new LangMap([
            'en' => 'Check if school is verified.',
            'cs' => '',
        ]);
    }

    protected function innerRun(TestLogger $logger, Model $model, string $id): void
    {
        if (!$model->verified) {
            $logger->log(new TestMessage($id, _('School is no verified'), Message::LVL_ERROR));
        }
    }

    public function getId(): string
    {
        return 'schoolVerified';
    }
}

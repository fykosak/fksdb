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
final class StudyYearFillTest extends Test
{
    public function getTitle(): Title
    {
        return new Title(null, _('Filled study years'));
    }

    public function getDescription(): ?LangMap
    {
        return new LangMap([
            'en' => 'Checks if school has filled any of study types (study_* fields).',
            'cs' => '',
        ]);
    }

    protected function innerRun(TestLogger $logger, Model $model, string $id): void
    {
        if ($model->active && !$model->study_p && !$model->study_h && !$model->study_u) {
            $logger->log(new TestMessage($id, _('Missing study years'), Message::LVL_ERROR));
        }
    }

    public function getId(): string
    {
        return 'schoolStudyYearFill';
    }
}

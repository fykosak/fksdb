<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

class SubmitSource extends FakeStringEnum implements EnumColumn
{
    public const UPLOAD = 'upload';
    public const POST = 'post';
    public const QUIZ = 'quiz';

    public function badge(): Html
    {
        return Html::el('span');
    }

    public function label(): string
    {
        return '';
    }

    public static function cases(): array
    {
        return [
            new static(self::POST),
            new static(self::QUIZ),
            new static(self::UPLOAD),
        ];
    }

    public function getBehaviorType(): string
    {
        throw new NotImplementedException();
    }
}

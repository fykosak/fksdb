<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

final class SubmitSource extends FakeStringEnum implements EnumColumn
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
            new self(self::POST),
            new self(self::QUIZ),
            new self(self::UPLOAD),
        ];
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting;

use FKSDB\Models\Exceptions\NotImplementedException;
use Fykosak\Utils\Logging\Message;
use Nette\Utils\Html;

class TestLog extends Message
{
    public function __construct(
        public string $testName,
        public string|Html|null $text,
        public TestLogLevel $level,
        public ?Html $detail = null
    ) {
    }

    /**
     * @throws NotImplementedException
     */
    public function createHtmlIcon(): Html
    {
        $icon = Html::el('span');
        $icon->addAttributes([
            'class' => $this->level->mapLevelToIcon(),
        ]);
        return Html::el('span')->addAttributes([
            'class' => 'text-' . $this->level->value,
            'title' => (string)$this->text,
        ])->addHtml($icon);
    }
}

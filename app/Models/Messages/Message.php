<?php

namespace FKSDB\Models\Messages;

use FKSDB\Models\ArrayAble;
use FKSDB\Models\Logging\Logger;
use Nette\SmartObject;

class Message implements ArrayAble {
    use SmartObject;

    public const LVL_DANGER = Logger::ERROR;
    public const LVL_SUCCESS = Logger::SUCCESS;
    public const LVL_WARNING = Logger::WARNING;
    public const LVL_INFO = Logger::INFO;

    public string $text;

    public string $level;

    public function __construct(string $message, string $level) {
        $this->text = $message;
        $this->level = $level;
    }

    public function __toArray(): array {
        return [
            'text' => $this->text,
            'message' => $this->text,
            'level' => $this->level,
        ];
    }
}

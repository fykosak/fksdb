<?php

namespace FKSDB\Messages;

use FKSDB\ArrayAble;
use FKSDB\Logging\ILogger;
use Nette\SmartObject;

/**
 * Class Message
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Message implements ArrayAble {
    use SmartObject;

    public const LVL_DANGER = ILogger::ERROR;
    public const LVL_SUCCESS = ILogger::SUCCESS;
    public const LVL_WARNING = ILogger::WARNING;
    public const LVL_INFO = ILogger::INFO;

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

<?php

namespace FKSDB\Messages;

use FKSDB\Logging\ILogger;
use Nette\SmartObject;

/**
 * Class Message
 * *
 */
class Message {
    use SmartObject;

    public const LVL_DANGER = ILogger::ERROR;
    public const LVL_SUCCESS = ILogger::SUCCESS;
    public const LVL_WARNING = ILogger::WARNING;
    public const LVL_INFO = ILogger::INFO;
    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $level;

    /**
     * Message constructor.
     * @param string $message
     * @param string $level
     */
    public function __construct(string $message, string $level) {
        $this->message = $message;
        $this->level = $level;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getText(): string {
        return $this->message;
    }

    /**
     * @param string $message
     * @return void
     * @deprecated
     */
    public function setText(string $message): void {
        $this->message = $message;
    }

    public function setMessage(string $message): void {
        $this->message = $message;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function getLevel(): string {
        return $this->level;
    }

    public function setLevel(string $level): void {
        $this->level = $level;
    }

    public function __toArray(): array {
        return [
            'text' => $this->message,
            'message' => $this->message,
            'level' => $this->level,
        ];
    }
}

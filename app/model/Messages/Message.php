<?php

namespace FKSDB\Messages;

use FKSDB\Logging\ILogger;
use Nette\SmartObject;

/**
 * Class Message
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Message {
    use SmartObject;

    const LVL_DANGER = ILogger::ERROR;
    const LVL_SUCCESS = ILogger::SUCCESS;
    const LVL_WARNING = ILogger::WARNING;
    const LVL_INFO = ILogger::INFO;

    private string $message;

    private string $level;

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

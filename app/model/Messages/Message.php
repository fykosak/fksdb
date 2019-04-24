<?php

namespace FKSDB\Messages;

/**
 * Class Message
 * @package FKSDB\Messages
 */
class Message {
    const LVL_DANGER = 'danger';
    const LVL_SUCCESS = 'success';
    const LVL_WARNING = 'warning';
    const LVL_INFO = 'info';
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
     * @deprecated
     */
    public function setText(string $message) {
        $this->message = $message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message) {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage(): string {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getLevel(): string {
        return $this->level;
    }

    /**
     * @param string $level
     */
    public function setLevel(string $level) {
        $this->level = $level;
    }

    /**
     * @return array
     */
    public function __toArray(): array {
        return [
            'text' => $this->message,
            'message' => $this->message,
            'level' => $this->level,
        ];
    }

}

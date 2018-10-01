<?php

namespace FKSDB\Messages;

class Message {

    const LVL_DANGER = 'danger';
    const LVL_WARNING = 'warning';
    const LVL_INFO = 'info';
    const LVL_SUCCESS = 'success';
    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $level;

    public function __construct($text, $level) {
        $this->text = $text;
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text) {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getLevel() {
        return $this->level;
    }

    /**
     * @param string $level
     */
    public function setLevel(string $level) {
        $this->level = $level;
    }

    public function __toArray() {
        return [
            'text' => $this->text,
            'level' => $this->level,
        ];
    }

}

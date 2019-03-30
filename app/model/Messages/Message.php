<?php
namespace FKSDB\Messages;

/**
 * Class Message
 * @package FKSDB\Messages
 */
class Message {
    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $level;

    /**
     * Message constructor.
     * @param $text
     * @param $level
     */
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

    /**
     * @return array
     */
    public function __toArray() {
        return [
            'text' => $this->text,
            'level' => $this->level,
        ];
    }

}

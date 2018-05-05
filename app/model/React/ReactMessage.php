<?php

class ReactMessage {
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
    public function getText(): string {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void {
        $this->text = $text;
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
    public function setLevel(string $level): void {
        $this->level = $level;
    }

    public function __toArray() {
        return [
            'text' => $this->text,
            'level' => $this->level,
        ];
    }

}

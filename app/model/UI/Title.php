<?php

namespace FKSDB\UI;

/**
 * Class Title
 * *
 */
class Title {
    /** @var string */
    public $title;
    /** @var string */
    public $icon;

    /**
     * PageTitle constructor.
     * @param string $title
     * @param string $icon
     */
    public function __construct(string $title, string $icon = '') {
        $this->title = $title;
        $this->icon = $icon;
    }
}

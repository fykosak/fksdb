<?php

namespace FKSDB\UI;

/**
 * Class Title
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Title {

    public string $title;

    public string $icon;

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

<?php

namespace FKSDB\Components\Controls\Stalking\Helpers;

use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\UI\Control;
use Nette\Templating\FileTemplate;

/**
 * Class PersonLinkControl
 * @package FKSDB\Components\Controls\Stalking\Helpers
 * @property FileTemplate $template
 */
class PersonLinkControl extends Control {
    /**
     * @param ModelPerson $person
     * @param int $year
     * @param int $contestId
     */
    public function render(ModelPerson $person) {
        $this->template->person = $person;
        $this->template->setFile(__DIR__ . '/PersonLinkControl.latte');
        $this->template->render();
    }
}

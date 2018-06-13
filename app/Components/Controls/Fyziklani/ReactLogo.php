<?php

namespace FKSDB\Components\Controls\Fyziklani;

use Nette\Application\UI\Control;

use Nette\Templating\FileTemplate;

/**
 * Class ReactComponent
 * @property FileTemplate template
 */
class ReactLogo extends Control {

    /**
     * @throws \Nette\Utils\JsonException
     */
    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ReactLogo.latte');
        $this->template->render();
    }
}

<?php

namespace Mail;

use Nette\Application\UI\Control;
use Nette\Latte\Engine;
use Nette\Templating\FileTemplate;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class MailTemplateFactory {

    /**
     * @var string without trailing slash
     */
    private $templateDir;

    function __construct($templateDir) {
        $this->templateDir = $templateDir;
    }

    /**
     * @param string $lang ISO 639-1
     */
    public function createLoginInvitation(Control $control, $lang) {
        return $this->createFromFile('loginInvitation', $lang, $control);
    }

    /**
     * @param string $lang ISO 639-1
     */
    public function createPasswordRecovery(Control $control, $lang) {
        return $this->createFromFile('passwordRecovery', $lang, $control);
    }

    private function createFromFile($filename, $lang, Control $control) {
        $file = $this->templateDir . DIRECTORY_SEPARATOR . "$filename.$lang.latte";
        $template = new FileTemplate($file);
        $template->registerHelperLoader('Nette\Templating\Helpers::loader');
        $template->registerFilter(new Engine());
        $template->control = $template->_control = $control;

        return $template;
    }

}

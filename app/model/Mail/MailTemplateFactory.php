<?php

namespace Mail;

use BasePresenter;
use Nette\Application\Application;
use Nette\Application\UI\Control;
use Nette\InvalidArgumentException;
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

    /**
     * @var Application
     */
    private $application;

    function __construct($templateDir, Application $application) {
        $this->templateDir = $templateDir;
        $this->application = $application;
    }

    /**
     * @internal For automated testing only.
     * @param $application
     */
    public function injectApplication($application) {
        $this->application = $application;
    }

    /**
     * @param Control|null $control
     * @param string $lang ISO 639-1
     * @return FileTemplate
     */
    public function createLoginInvitation(Control $control = null, $lang = null) {
        return $this->createFromFile('loginInvitation', $lang, $control);
    }

    /**
     * @param Control|null $control
     * @param string $lang ISO 639-1
     * @return FileTemplate
     */
    public function createPasswordRecovery(Control $control = null, $lang = null) {
        return $this->createFromFile('passwordRecovery', $lang, $control);
    }

    public final function createFromFile($filename, $lang = null, Control $control = null) {
        $presenter = $this->application->getPresenter();
        if (($lang === null || $control === null) && !$presenter instanceof BasePresenter) {
            throw new InvalidArgumentException("Expecting BasePresenter, got " . ($presenter ? get_class($presenter) : (string)$presenter));
        }
        if ($lang === null) {
            $lang = $presenter->getLang();
        }
        if ($control === null) {
            $control = $presenter;
        }

        $file = $this->templateDir . DIRECTORY_SEPARATOR . "$filename.$lang.latte";
        if (!file_exists($file)) {
            throw new InvalidArgumentException("Cannot find template '$filename.$lang'.");
        }
        \var_dump($presenter);
        $template = new FileTemplate($file);
        $template->registerHelperLoader('Nette\Templating\Helpers::loader');
        $template->registerFilter(new Engine());
        $template->control = $template->_control = $control;
        if ($presenter instanceof BasePresenter) {
            $template->baseUri = $presenter->getContext()->getByType('Nette\Http\IRequest')->getUrl()->getBaseUrl();
        }

        return $template;
    }

}

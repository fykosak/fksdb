<?php

namespace Mail;

use BasePresenter;
use Nette\Application\Application;
use Nette\Application\UI\Control;
use Nette\Http\IRequest;
use Nette\InvalidArgumentException;
use Nette\Latte\Engine;
use Nette\Localization\ITranslator;
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
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * MailTemplateFactory constructor.
     * @param $templateDir
     * @param Application $application
     * @param ITranslator $translator
     */
    function __construct(string $templateDir, Application $application, ITranslator $translator) {
        $this->templateDir = $templateDir;
        $this->application = $application;
        $this->translator = $translator;
    }

    /**
     * @param Application $application
     * @internal For automated testing only.
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

    /**
     * @param string $templateFile
     * @param string $lang
     * @param array $data
     * @return FileTemplate
     */
    public function createWithParameters(string $templateFile, string $lang = null, array $data = []) {
        $template = $this->createFromFile($templateFile, $lang);
        $template->setTranslator($this->translator);
        foreach ($data as $key => $value) {
            $template->{$key} = $value;
        }
        return $template;
    }

    /**
     * @param $filename
     * @param null $lang
     * @param Control|null $control
     * @return FileTemplate
     */
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
        $template = new FileTemplate($file);
        $template->registerHelperLoader('Nette\Templating\Helpers::loader');
        $template->registerFilter(new Engine());
        $template->control = $template->_control = $control;
        if ($presenter instanceof BasePresenter) {
            $template->baseUri = $presenter->getContext()->getByType(IRequest::class)->getUrl()->getBaseUrl();
        }

        return $template;
    }

}

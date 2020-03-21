<?php

namespace Mail;

use BasePresenter;
use Nette\Application\Application;
use Nette\Http\IRequest;
use Nette\InvalidArgumentException;
use Nette\Latte\Engine;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @author Michal Cervenak <miso@fykos.cz>
 */
class MailTemplateFactory {

    /** @var string without trailing slash */
    private $templateDir;
    /** @var Application */
    private $application;
    /** @var ITranslator */
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
     * @deprecated
     * TODO remove this!
     */
    public function injectApplication($application) {
        $this->application = $application;
    }

    /**
     * @param string $lang ISO 639-1
     * @param array $data
     * @return FileTemplate
     */
    public function createLoginInvitation(string $lang = null, array $data = []): FileTemplate {
        return $this->createWithParameters('loginInvitation', $lang, $data);
    }

    /**
     * @param string $lang ISO 639-1
     * @param array $data
     * @return FileTemplate
     */
    public function createPasswordRecovery(string $lang = null, array $data = []): FileTemplate {
        return $this->createWithParameters('passwordRecovery', $lang, $data);
    }

    /**
     * @param string $templateFile
     * @param string $lang ISO 639-1
     * @param array $data
     * @return FileTemplate
     */
    public function createWithParameters(string $templateFile, string $lang = null, array $data = []): FileTemplate {
        $template = $this->createFromFile($templateFile, $lang);
        $template->setTranslator($this->translator);
        foreach ($data as $key => $value) {
            $template->{$key} = $value;
        }
        return $template;
    }

    /**
     * @param $filename
     * @param string $lang ISO 639-1
     * @return FileTemplate
     */
    public final function createFromFile(string $filename, string $lang = null): FileTemplate {
        $presenter = $this->application->getPresenter();
        if (($lang === null) && !$presenter instanceof BasePresenter) {
            throw new InvalidArgumentException("Expecting BasePresenter, got " . ($presenter ? get_class($presenter) : (string)$presenter));
        }
        if ($lang === null) {
            $lang = $presenter->getLang();
        }
        $control = $presenter;

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

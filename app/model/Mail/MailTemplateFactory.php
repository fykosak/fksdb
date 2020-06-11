<?php

namespace Mail;

use BasePresenter;
use MockEnvironment\MockApplication;
use Nette\Application\Application;
use Nette\Application\BadRequestException;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\ITemplateFactory;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\InvalidArgumentException;
use Nette\Localization\ITranslator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @author Michal Cervenak <miso@fykos.cz>
 */
class MailTemplateFactory {

    /** @var string without trailing slash */
    private $templateDir;
    /** @var Application|MockApplication */
    private $application;
    /** @var ITranslator */
    private $translator;
    /** @var IRequest */
    private $request;
    /** @var ITemplateFactory */
    private $templateFactory;

    /**
     * MailTemplateFactory constructor.
     * @param string $templateDir
     * @param Application $application
     * @param ITranslator $translator
     * @param IRequest $request
     * @param ITemplateFactory $templateFactory
     */
    public function __construct(string $templateDir, Application $application, ITranslator $translator, IRequest $request, ITemplateFactory $templateFactory) {
        $this->templateDir = $templateDir;
        $this->application = $application;
        $this->translator = $translator;
        $this->request = $request;
        $this->templateFactory = $templateFactory;
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
     * @return ITemplate
     * @throws BadRequestException
     */
    public function createLoginInvitation(string $lang = null, array $data = []): ITemplate {
        return $this->createWithParameters('loginInvitation', $lang, $data);
    }

    /**
     * @param string $lang ISO 639-1
     * @param array $data
     * @return ITemplate
     * @throws BadRequestException
     */
    public function createPasswordRecovery(string $lang = null, array $data = []): ITemplate {
        return $this->createWithParameters('passwordRecovery', $lang, $data);
    }

    /**
     * @param string $templateFile
     * @param string $lang ISO 639-1
     * @param array $data
     * @return ITemplate
     * @throws BadRequestException
     */
    public function createWithParameters(string $templateFile, string $lang = null, array $data = []): ITemplate {
        $template = $this->createFromFile($templateFile, $lang);
        $template->setTranslator($this->translator);
        foreach ($data as $key => $value) {
            $template->{$key} = $value;
        }
        return $template;
    }

    /**
     * @param string $filename
     * @param string $lang ISO 639-1
     * @return ITemplate
     * @throws BadRequestException
     */
    final public function createFromFile(string $filename, string $lang = null): ITemplate {
        /** @var Presenter $presenter */
        $presenter = $this->application->getPresenter();
        if (($lang === null) && !$presenter instanceof BasePresenter) {
            throw new InvalidArgumentException("Expecting BasePresenter, got " . ($presenter ? get_class($presenter) : (string)$presenter));
        }
        if ($lang === null) {
            $lang = $presenter->getLang();
        }

        $file = $this->templateDir . DIRECTORY_SEPARATOR . "$filename.$lang.latte";
        if (!file_exists($file)) {
            throw new InvalidArgumentException("Cannot find template '$filename.$lang'.");
        }
        $template = $this->templateFactory->createTemplate();
        $template->setFile($file);
        $template->control = $template->_control = $presenter;
        $template->baseUri = $this->request->getUrl()->getBaseUrl();
        return $template;
    }

}

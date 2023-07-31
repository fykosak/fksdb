<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Application\Application;
use Nette\Application\UI\TemplateFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Http\IRequest;
use Nette\InvalidArgumentException;

class MailTemplateFactory
{

    /** without trailing slash */
    private string $templateDir;
    /** @var Application */
    private $application;

    private GettextTranslator $translator;
    private IRequest $request;
    private TemplateFactory $templateFactory;

    public function __construct(
        string $templateDir,
        TemplateFactory $templateFactory,
        Application $application,
        GettextTranslator $translator,
        IRequest $request
    ) {
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
    final public function injectApplication($application): void
    {
        $this->application = $application;
    }

    /**
     * @throws BadTypeException
     */
    public function renderLoginInvitation(array $data, string $lang): string
    {
        return $this->create($lang)->renderToString(__DIR__ . '/loginInvitation.latte', $data);
    }

    /**
     * @throws BadTypeException
     */
    public function renderPasswordRecovery(array $data, string $lang): string
    {
        return $this->create($lang)->renderToString(__DIR__ . '/recovery.latte', $data);
    }

    /**
     * @throws BadTypeException
     */
    public function renderChangeEmailOld(array $data, Language $lang): string
    {
        return $this->create($lang->value)
            ->renderToString(__DIR__ . '/changeEmail.old.latte', $data);
    }

    /**
     * @throws BadTypeException
     */
    public function renderChangeEmailNew(array $data, Language $lang): string
    {
        return $this->create($lang->value)
            ->renderToString(__DIR__ . '/changeEmail.new.latte', $data);
    }

    /**
     * @throws BadTypeException
     */
    public function renderWithParameters(string $templateFile, ?string $lang, array $data = []): string
    {
        return $this->create($this->resolverLang($lang))
            ->renderToString($this->resolverFileName($templateFile, $lang), $data);
    }

    private function resolverLang(?string $lang): string
    {
        return $lang ?? $this->translator->lang;
    }

    private function resolverFileName(string $filename, ?string $lang): string
    {
        if (file_exists($filename)) {
            return $filename;
        }

        $lang = $this->resolverLang($lang);
        $filename = "$filename.$lang.latte";
        if (file_exists($filename)) {
            return $filename;
        }

        $filename = $this->templateDir . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($filename)) {
            return $filename;
        }
        throw new InvalidArgumentException(sprintf(_('Cannot find template "%s.%s".'), $filename, $lang));
    }

    /**
     * @throws BadTypeException
     */
    private function create(string $lang): Template
    {
        $presenter = $this->application->getPresenter();
        if (!$presenter instanceof BasePresenter) {
            throw new BadTypeException(BasePresenter::class, $presenter);
        }
        $template = $this->templateFactory->createTemplate();

        if (!$template instanceof Template) {
            throw new BadTypeException(Template::class, $template);
        }
        $template->getLatte()->addProvider('uiControl', $presenter);
        $template->control = $presenter;
        $template->baseUri = $this->request->getUrl()->getBaseUrl();
        $template->setTranslator($this->translator, $lang);
        return $template;
    }
}

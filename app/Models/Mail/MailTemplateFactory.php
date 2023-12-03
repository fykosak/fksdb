<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Tests\Test;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Modules\Core\Language;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Localization\GettextTranslator;
use Fykosak\Utils\Logging\MemoryLogger;
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
     * @phpstan-param array{token:AuthTokenModel} $data
     */
    public function renderLoginInvitation(array $data, Language $lang): string
    {
        return $this->create($lang)->renderToString(__DIR__ . '/loginInvitation.latte', $data);
    }

    /**
     * @throws BadTypeException
     * @phpstan-param array{token:AuthTokenModel,person:PersonModel,lang:string} $data
     */
    public function renderPasswordRecovery(array $data, Language $lang): string
    {
        return $this->create($lang)->renderToString(__DIR__ . '/recovery.latte', $data);
    }

    /**
     * @throws BadTypeException
     * @phpstan-param array{lang:Language,person:PersonModel,newEmail:string} $data
     */
    public function renderChangeEmailOld(array $data, Language $lang): string
    {
        return $this->create($lang)
            ->renderToString(__DIR__ . '/changeEmail.old.latte', $data);
    }

    /**
     * @throws BadTypeException
     * @phpstan-param array{lang:Language,person:PersonModel,newEmail:string,token:AuthTokenModel} $data
     */
    public function renderChangeEmailNew(array $data, Language $lang): string
    {
        return $this->create($lang)
            ->renderToString(__DIR__ . '/changeEmail.new.latte', $data);
    }

    /**
     * @throws BadTypeException
     * @phpstan-param array{logger:MemoryLogger} $data
     */
    public function renderReport(array $data, Language $lang): string
    {
        return $this->create($lang)
            ->renderToString(__DIR__ . '/report.latte', $data);
    }
    /**
     * @throws BadTypeException
     * @phpstan-template TModel of Model
     * @phpstan-param array{model:TModel,tests:Test<TModel>[]} $data
     */
    public function renderReport2(array $data, Language $lang): string
    {
        return $this->create($lang)
            ->renderToString(__DIR__ . '/report2.latte', $data);
    }

    /**
     * @throws BadTypeException
     * @phpstan-param array<string,mixed> $data
     */
    public function renderWithParameters(string $templateFile, ?Language $lang, array $data = []): string
    {
        return $this->create($this->resolverLang($lang))
            ->renderToString($this->resolverFileName($templateFile, $lang), $data);
    }

    private function resolverLang(?Language $lang): Language
    {
        return $lang ?? Language::from($this->translator->lang);
    }

    private function resolverFileName(string $filename, ?Language $lang): string
    {
        if (file_exists($filename)) {
            return $filename;
        }

        $lang = $this->resolverLang($lang);
        $filename = "$filename.$lang->value.latte";
        if (file_exists($filename)) {
            return $filename;
        }

        $filename = $this->templateDir . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($filename)) {
            return $filename;
        }
        throw new InvalidArgumentException(sprintf(_('Cannot find template "%s.%s".'), $filename, $lang->value));
    }

    /**
     * @throws BadTypeException
     */
    private function create(Language $lang): Template
    {
        $presenter = $this->application->getPresenter();
        if ($presenter && !$presenter instanceof BasePresenter) {
            throw new BadTypeException(BasePresenter::class, $presenter);
        }
        $template = $this->templateFactory->createTemplate();

        if (!$template instanceof Template) {
            throw new BadTypeException(Template::class, $template);
        }
        $template->getLatte()->addProvider('uiControl', $presenter);
        $template->control = $presenter;
        $template->baseUri = $this->request->getUrl()->getBaseUrl();
        $template->setTranslator($this->translator, $lang->value);
        return $template;
    }
}

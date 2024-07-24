<?php

declare(strict_types=1);

namespace FKSDB\Models\Email;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Application\Application;
use Nette\Application\UI\TemplateFactory as LatteFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Http\IRequest;
use Nette\InvalidArgumentException;

/**
 * @phpstan-type TRenderedData = array{text:string,subject:string}
 */
class TemplateFactory
{
    /** @var Application */
    private $application;

    private GettextTranslator $translator;
    private IRequest $request;
    private LatteFactory $templateFactory;

    public function __construct(
        LatteFactory $templateFactory,
        Application $application,
        GettextTranslator $translator,
        IRequest $request
    ) {
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
     * @phpstan-template TData of array
     * @phpstan-param TData $data
     * @phpstan-return TRenderedData
     * @throws BadTypeException
     */
    public function renderWithParameters(string $templateFile, array $data, ?Language $lang): array
    {
        $lang = $lang ?? Language::from($this->translator->lang);
        if (!file_exists($templateFile)) {
            throw new InvalidArgumentException(sprintf(_('Cannot find template "%s".'), $templateFile));
        }
        return [
            'subject' => $this->create($lang)->renderToString(
                __DIR__ . '/subject.latte',
                array_merge(['templateFile' => $templateFile], $data)
            ),
            'text' => $this->create($lang)->renderToString($templateFile, $data),
        ];
    }

    /**
     * @throws BadTypeException
     * @phpstan-param array{
     *     model: EmailMessageModel,
     *     code:string|null,
     *     token:AuthTokenModel|null,
     *     } $data
     */
    public function addContainer(EmailMessageModel $model, array $data): string
    {
        if ($model->topic->isSpam()) {
            $template = __DIR__ . '/Containers/spam.latte';
        } else {
            $template = __DIR__ . '/Containers/noSpam.latte';
        }
        return $this->create($model->lang)->renderToString($template, $data);
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
        $template->baseUrl = $this->request->getUrl()->getBaseUrl();
        $template->setTranslator($this->translator, $lang->value);
        return $template;
    }
}

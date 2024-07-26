<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Application\Application;
use Nette\Application\UI\TemplateFactory as LatteFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\InvalidArgumentException;

/**
 * @phpstan-template TTemplateParam of array
 * @phpstan-template TSchema of array
 * @phpstan-type TRenderedData = array{text:string,subject:string}
 * @phpstan-import-type TMessageData from EmailMessageService
 */
abstract class EmailSource
{
    protected Container $container;
    private EmailMessageService $emailMessageService;
    private GettextTranslator $translator;
    private IRequest $request;
    private LatteFactory $latteTemplateFactory;
    private Application $application;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
        $this->container = $container;
    }

    public function inject(
        EmailMessageService $emailMessageService,
        LatteFactory $latteTemplateFactory,
        Application $application,
        GettextTranslator $translator,
        IRequest $request
    ): void {
        $this->emailMessageService = $emailMessageService;
        $this->application = $application;
        $this->translator = $translator;
        $this->request = $request;
        $this->latteTemplateFactory = $latteTemplateFactory;
    }


    /**
     * @phpstan-return array{
     *     template: array{
     *          data: TTemplateParam,
     *          file: string,
     *      },
     *      lang: Language,
     *      data: array{
     *          recipient_person_id:int,
     *          sender:string,
     *          reply_to?:string,
     *          carbon_copy?:string,
     *          blind_carbon_copy?:string,
     *          priority?:int|bool,
     *      }|array{
     *          recipient:string,
     *          sender:string,
     *          reply_to?:string,
     *          carbon_copy?:string,
     *          blind_carbon_copy?:string,
     *          priority?:int|bool,
     *      },
     *    }[]
     * @phpstan-param TSchema $params
     */
    abstract protected function getSource(array $params): array;

    /**
     * @phpstan-return TMessageData[]
     * @phpstan-param (int|bool|string)[] $params
     * @throws BadTypeException
     */
    public function createEmails(array $params): array
    {
        // $processor = new Processor();
        // $params = $processor->process(new Structure($this->getExpectedParams()), $params);
        $return = [];
        foreach ($this->getSource($params) as $sourceItem) {
            $return[] = array_merge(
                $this->render(
                    $sourceItem['template']['file'],
                    $sourceItem['template']['data'],
                    $sourceItem['lang']
                ),
                $sourceItem['data']
            );
        }
        return $return;//@phpstan-ignore-line
    }
    /**
     * @phpstan-param TSchema $params
     * @throws BadTypeException
     */
    public function createAndSend(array $params): void
    {
        foreach ($this->createEmails($params) as $email) {
            $this->emailMessageService->addMessageToSend($email);
        }
    }

    /**
     * @phpstan-param TTemplateParam $data
     * @phpstan-return TRenderedData
     * @throws BadTypeException
     */
    private function render(string $templateFile, array $data, Language $lang): array
    {
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
     */
    private function create(Language $lang): Template
    {
        $presenter = $this->application->getPresenter();
        if ($presenter && !$presenter instanceof BasePresenter) {
            throw new BadTypeException(BasePresenter::class, $presenter);
        }
        $template = $this->latteTemplateFactory->createTemplate();

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

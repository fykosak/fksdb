<?php

declare(strict_types=1);

namespace FKSDB\Models\Email;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Modules\Core\Language;
use Nette\DI\Container;
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
    private TemplateFactory $templateFactory;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
        $this->container = $container;
    }

    public function inject(
        TemplateFactory $templateFactory,
        EmailMessageService $emailMessageService
    ): void {
        $this->templateFactory = $templateFactory;
        $this->emailMessageService = $emailMessageService;
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
            'subject' => $this->templateFactory->create($lang)->renderToString(
                __DIR__ . '/subject.latte',
                array_merge(['templateFile' => $templateFile], $data)
            ),
            'text' => $this->templateFactory->create($lang)->renderToString($templateFile, $data),
        ];
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-template TTemplateParam of array
 * @phpstan-template TSchema of (int|bool|string)[]
 * @phpstan-import-type TMessageData from EmailMessageService
 */
abstract class MailSource
{
    protected MailTemplateFactory $mailTemplateFactory;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
    }

    public function inject(MailTemplateFactory $mailTemplateFactory): void
    {
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    /**
     * @phpstan-return ('int'|'string'|'bool')[]
     */
    abstract public function getExpectedParams(): array;

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
                $this->mailTemplateFactory->renderWithParameters(
                    $sourceItem['template']['file'],
                    $sourceItem['template']['data'],
                    $sourceItem['lang']
                ),
                $sourceItem['data']
            );
        }
        return $return;//@phpstan-ignore-line
    }

    abstract public function title(): Title;

    abstract public function description(): LocalizedString;
}

<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source;

use FKSDB\Models\Email\TemplateFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-template TSource of mixed
 * @phpstan-template TTemplateParam of array
 * @phpstan-template TSchema of array
 * @phpstan-import-type TMessageData from EmailMessageService
 */
abstract class MailSource
{
    protected TemplateFactory $mailTemplateFactory;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
    }

    public function inject(TemplateFactory $mailTemplateFactory): void
    {
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    /**
     * @phpstan-return ('int'|'string'|'bool')[]
     */
    abstract public function getExpectedParams(): array;

    /**
     * @phpstan-return iterable<TSource>
     * @phpstan-param TSchema $params
     */
    abstract protected function getSource(array $params): iterable;

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
                    $this->getTemplateFile($sourceItem), //      $sourceItem['template']['file'],
                    $this->getTemplateParams($sourceItem), //$sourceItem['template']['data'],
                    $this->getEmailLang($sourceItem)
                ),
                $this->getEmailData($sourceItem)
            );
        }
        return $return;//@phpstan-ignore-line
    }

    /**
     * @phpstan-param TSource $source
     */
    abstract protected function getTemplateFile($source): string;

    /**
     * @phpstan-param TSource $source
     * @phpstan-return TTemplateParam
     */
    abstract protected function getTemplateParams($source): array;

    /**
     * @phpstan-param TSource $source
     * @phpstan-return array{
     *           recipient_person_id:int,
     *           sender:string,
     *           reply_to?:string,
     *           carbon_copy?:string,
     *           blind_carbon_copy?:string,
     *           priority?:int|bool,
     *       }|array{
     *           recipient:string,
     *           sender:string,
     *           reply_to?:string,
     *           carbon_copy?:string,
     *           blind_carbon_copy?:string,
     *           priority?:int|bool,
     *       },
     */
    abstract protected function getEmailData($source): array;

    /**
     * @phpstan-param TSource $source
     */
    abstract protected function getEmailLang($source): Language;

    abstract public function title(): Title;

    abstract public function description(): LocalizedString;//@phpstan-ignore-line
}

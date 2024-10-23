<?php

declare(strict_types=1);

namespace FKSDB\Models\Email;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EmailMessageState;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\Transitions\Machine\EmailMachine;
use FKSDB\Models\Transitions\TransitionsMachineFactory;
use FKSDB\Modules\Core\Language;
use Nette\DI\Container;
use Nette\InvalidArgumentException;

/**
 * @phpstan-template TTemplateParam of array
 * @phpstan-template TSchema of array
 * @phpstan-type TRenderedData = array{inner_text:string,subject:string}
 * @phpstan-import-type TMessageData from EmailMessageService
 */
abstract class EmailSource
{
    protected Container $container;
    private EmailMessageService $emailMessageService;
    private TemplateFactory $templateFactory;
    private EmailMachine $machine;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
        $this->container = $container;
    }

    public function inject(
        TemplateFactory $templateFactory,
        EmailMessageService $emailMessageService,
        TransitionsMachineFactory $transitionsMachineFactory
    ): void {
        $this->templateFactory = $templateFactory;
        $this->emailMessageService = $emailMessageService;
        $this->machine = $transitionsMachineFactory->getEmailMachine();
    }

    /**
     * @phpstan-return array{
     *     template: array{
     *          data: TTemplateParam,
     *          file: string,
     *      },
     *      data: array{
     *          recipient_person_id:int,
     *          sender:string,
     *          reply_to?:string,
     *          carbon_copy?:string,
     *          blind_carbon_copy?:string,
     *          priority?:int|bool,
     *          lang:Language,
     *          topic:EmailMessageTopic,
     *      }|array{
     *          recipient:string,
     *          sender:string,
     *          reply_to?:string,
     *          carbon_copy?:string,
     *          blind_carbon_copy?:string,
     *          priority?:int|bool,
     *          lang:Language,
     *          topic:EmailMessageTopic,
     * },
     *    }[]
     * @phpstan-param TSchema $params
     */
    abstract protected function getSource(array $params): array;

    /**
     * @phpstan-return TMessageData[]
     * @phpstan-param TSchema $params
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
                    $sourceItem['data']['lang']
                ),
                $sourceItem['data']
            );
        }
        return $return;//@phpstan-ignore-line
    }

    /**
     * @phpstan-param TSchema $params
     * @throws BadTypeException
     * @throws \Throwable
     */
    public function createAndSend(array $params): void
    {
        $transition = $this->machine->getTransitions()
            ->filterBySource(EmailMessageState::Ready)
            ->filterByTarget(EmailMessageState::Waiting)
            ->select();
        foreach ($this->createEmails($params) as $email) {
            $model = $this->emailMessageService->addMessageToSend($email);
            $holder = $this->machine->createHolder($model);
            $transition->execute($holder);
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
            'subject' => $this->templateFactory->create($lang)(
                __DIR__ . '/subject.latte',
                array_merge(['templateFile' => $templateFile], $data)
            ),
            'inner_text' => $this->templateFactory->create($lang)($templateFile, $data),
        ];
    }
}

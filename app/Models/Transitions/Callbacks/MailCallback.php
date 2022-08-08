<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Fykosak\NetteORM\ReferencedAccessor;

class MailCallback implements TransitionCallback
{

    protected EmailMessageService $emailMessageService;
    protected MailTemplateFactory $mailTemplateFactory;
    protected string $templateFile;
    protected array $emailData;

    public function __construct(
        string $templateFile,
        array $emailData,
        EmailMessageService $emailMessageService,
        MailTemplateFactory $mailTemplateFactory
    ) {
        $this->templateFile = $templateFile;
        $this->emailData = $emailData;
        $this->emailMessageService = $emailMessageService;
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    /**
     * @throws BadTypeException
     */
    public function __invoke(ModelHolder $holder, ...$args): void
    {
        /** @var PersonModel|null $person */
        $person = ReferencedAccessor::accessModel($holder->getModel(), PersonModel::class);
        if (is_null($person)) {
            throw new BadTypeException(PersonModel::class, $person);
        }
        $data = $this->emailData;
        $data['recipient'] = $person->getInfo()->email;

        $data['text'] = (string)$this->mailTemplateFactory->createWithParameters(
            $this->templateFile,
            $person->getPreferredLang(),
            ['model' => $holder->getModel()]
        );
        $this->emailMessageService->addMessageToSend($data);
    }
}

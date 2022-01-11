<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServiceEmailMessage;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Fykosak\NetteORM\ReferencedAccessor;
use Fykosak\Utils\Localization\UnsupportedLanguageException;

class MailCallback implements TransitionCallback
{

    protected ServiceEmailMessage $serviceEmailMessage;
    protected MailTemplateFactory $mailTemplateFactory;
    protected string $templateFile;
    protected array $emailData;

    public function __construct(
        string $templateFile,
        array $emailData,
        ServiceEmailMessage $serviceEmailMessage,
        MailTemplateFactory $mailTemplateFactory
    ) {
        $this->templateFile = $templateFile;
        $this->emailData = $emailData;
        $this->serviceEmailMessage = $serviceEmailMessage;
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    /**
     * @throws BadTypeException
     * @throws UnsupportedLanguageException
     */
    public function __invoke(ModelHolder $holder, ...$args): void
    {
        /** @var ModelPerson|null $person */
        $person = ReferencedAccessor::accessModel($holder->getModel(), ModelPerson::class);
        if (is_null($person)) {
            throw new BadTypeException(ModelPerson::class, $person);
        }
        $data = $this->emailData;
        $data['recipient'] = $person->getInfo()->email;

        $data['text'] = (string)$this->mailTemplateFactory->createWithParameters(
            $this->templateFile,
            $person->getPreferredLang(),
            ['model' => $holder->getModel()]
        );
        $this->serviceEmailMessage->addMessageToSend($data);
    }
}

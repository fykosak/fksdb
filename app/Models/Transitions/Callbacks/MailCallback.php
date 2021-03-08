<?php

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Entity\CannotAccessModelException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Localization\UnsupportedLanguageException;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\ReferencedAccessor;
use FKSDB\Models\ORM\Services\ServiceEmailMessage;
use FKSDB\Models\Transitions\Holder\ModelHolder;

/**
 * Class MailCallback
 * @author Michal Červeňák <miso@fykos.cz>
 */
class MailCallback implements TransitionCallback {

    protected ServiceEmailMessage $serviceEmailMessage;
    protected MailTemplateFactory $mailTemplateFactory;
    protected string $templateFile;
    protected array $emailData;

    /**
     * MailCallback constructor.
     * @param string $templateFile
     * @param array $emailData
     * @param ServiceEmailMessage $serviceEmailMessage
     * @param MailTemplateFactory $mailTemplateFactory
     */
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
     * @param ModelHolder $model
     * @param mixed ...$args
     * @return void
     * @throws BadTypeException
     * @throws UnsupportedLanguageException|CannotAccessModelException
     */
    public function __invoke(ModelHolder $model, ...$args): void {
        $this->invoke($model, ...$args);
    }

    /**
     * @param ModelHolder $holder
     * @param mixed ...$args
     * @return void
     * @throws BadTypeException
     * @throws UnsupportedLanguageException
     * @throws CannotAccessModelException
     */
    public function invoke(ModelHolder $holder, ...$args): void {
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

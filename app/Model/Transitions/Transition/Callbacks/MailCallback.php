<?php

namespace FKSDB\Model\Transitions\Transition\Callbacks;

use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\Localization\UnsupportedLanguageException;
use FKSDB\Model\Mail\MailTemplateFactory;
use FKSDB\Model\ORM\Models\IPersonReferencedModel;
use FKSDB\Model\ORM\Services\ServiceEmailMessage;
use FKSDB\Model\Transitions\IStateModel;

/**
 * Class MailCallback
 * @author Michal Červeňák <miso@fykos.cz>
 */
class MailCallback implements ITransitionCallback {

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
     * @param IStateModel $model
     * @param mixed ...$args
     * @return void
     * @throws BadTypeException
     * @throws UnsupportedLanguageException
     */
    public function __invoke(IStateModel $model, ...$args): void {
        $this->invoke($model, ...$args);
    }

    /**
     * @param IStateModel $model
     * @param mixed ...$args
     * @return void
     * @throws BadTypeException
     * @throws UnsupportedLanguageException
     */
    public function invoke(IStateModel $model, ...$args): void {
        if (!$model instanceof IPersonReferencedModel) {
            throw new BadTypeException(IPersonReferencedModel::class, $model);
        }
        $person = $model->getPerson();
        $data = $this->emailData;
        $data['recipient'] = $person->getInfo()->email;

        $data['text'] = (string)$this->mailTemplateFactory->createWithParameters(
            $this->templateFile,
            $person->getPreferredLang(),
            ['model' => $model]
        );
        $this->serviceEmailMessage->addMessageToSend($data);
    }
}

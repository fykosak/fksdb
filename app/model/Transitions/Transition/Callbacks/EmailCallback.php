<?php

namespace FKSDB\Transitions\Callbacks;

use FKSDB\Transitions\IStateModel;
use Mail\MailTemplateFactory;
use Nette\Localization\ITranslator;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Templating\FileTemplate;

class EmailCallback extends AbstractCallback {
    /**
     * @var callable
     */
    private $optionsCallback;
    /**
     * @var FileTemplate
     */
    private $template;

    /**
     * @var IMailer
     */
    private $mailer;

    public function __construct(callable $optionsCallback, string $templateFile, ITranslator $translator, IMailer $mailer, MailTemplateFactory $mailTemplateFactory) {
        $this->mailer = $mailer;
        $this->optionsCallback = $optionsCallback;
        $this->createTemplate($templateFile, $translator, $mailTemplateFactory);
    }

    /**
     * @param string $templateFile
     * @param ITranslator $translator
     * @param MailTemplateFactory $mailTemplateFactory
     */
    private function createTemplate(string $templateFile, ITranslator $translator, MailTemplateFactory $mailTemplateFactory) {
        $template = $mailTemplateFactory->createFromFile($templateFile);
        $template->setTranslator($translator);
        $this->template = $template;
    }

    /**
     * @param IStateModel|null $model
     */
    protected function evaluate(IStateModel $model = null) {
        /**
         * @var $message Message
         */
        $message = ($this->optionsCallback)($model);

        $this->template->model = $model;

        $message->setHtmlBody($this->template);
        $this->mailer->send($message);
    }
}

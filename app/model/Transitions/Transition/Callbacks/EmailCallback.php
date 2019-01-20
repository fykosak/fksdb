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
     * @var IMailer
     */
    private $mailer;
    /**
     * @var string
     */
    private $templateFile;
    /**
     * @var MailTemplateFactory
     */
    private $mailTemplateFactory;
    /**
     * @var ITranslator
     */
    private $translator;

    public function __construct(callable $optionsCallback, string $templateFile, ITranslator $translator, IMailer $mailer, MailTemplateFactory $mailTemplateFactory) {
        $this->mailer = $mailer;
        $this->optionsCallback = $optionsCallback;
        $this->templateFile = $templateFile;
        $this->translator = $translator;
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    /**
     * @return FileTemplate
     */
    private function createTemplate(): FileTemplate {
        $template = $this->mailTemplateFactory->createFromFile($this->templateFile);
        $template->setTranslator($this->translator);
        return $template;
    }

    /**
     * @param IStateModel|null $model
     */
    protected function evaluate(IStateModel $model = null) {
        $template = $this->createTemplate();
        /**
         * @var $message Message
         */
        $message = ($this->optionsCallback)($model);

        $template->model = $model;

        $message->setHtmlBody($template);
        $this->mailer->send($message);
    }
}

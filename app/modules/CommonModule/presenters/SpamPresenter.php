<?php

namespace CommonModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Utils\CSVParser;
use Mail\StreamTemplate;
use Nette\Application\BadRequestException;
use Nette\Forms\Form;
use Nette\Http\IRequest;
use Nette\Latte\Engine;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use function count;
use function explode;

/**
 * Class MailSenderPresenter
 * @package OrgModule
 */
class SpamPresenter extends BasePresenter {
    /**
     * @var IMailer
     */
    private $mailer;

    /**
     * @param IMailer $mailer
     */
    public function injectMailer(IMailer $mailer) {
        $this->mailer = $mailer;
    }

    public function titleDefault() {
        $this->setTitle(_('Mail sender'));
        $this->setIcon('fa fa-envelope');
    }

    public function authorizedDefault() {
        $this->setAuthorized(true);
        // $this->setAuthorized($this->getContestAuthorizator()->isAllowedForAnyContest('spam', 'default'));
    }

    /**
     * @throws BadRequestException
     */
    protected function createComponentCsvMailForm() {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addText('to', _('To'))->setRequired()->setOption('description', _('name of column'));

        $form->addText('subject', _('Subject'))
            ->setRequired(_('"Subject" is required'))
            ->setOption('description', _('name of column or text'));

        $form->addText('bcc', _('BCC'))->addCondition(Form::FILLED)->addRule(Form::EMAIL);
        $form->addText('cc', _('CC'))->addCondition(Form::FILLED)->addRule(Form::EMAIL);
        $form->addText('sender', _('Sender'))->setRequired(_('"From" is required'))->addRule(Form::EMAIL);
        $form->addText('reply_to', _('Replay to'))->setRequired(_('"Replay to" is required'))->addRule(Form::EMAIL);
        $form->addTextArea('text', _('Text'));
        $form->addUpload('file', _('File'))->setRequired(true);
        $form->addSubmit('submit', _('Send'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleCsvMailForm($form);
        };
        return $control;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentSimpleMailForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addText('to', _('To'))->setRequired()->setOption('description', _('Comma separated address. max 40 address'));

        $form->addText('subject', _('Subject'))
            ->setRequired(_('"Subject" is required'));

        $form->addText('bcc', _('BCC'))->addCondition(Form::FILLED)->addRule(Form::EMAIL);
        $form->addText('cc', _('CC'))->addCondition(Form::FILLED)->addRule(Form::EMAIL);
        $form->addText('from', _('From'))->setRequired(_('"From" is required'))->addRule(Form::EMAIL);
        $form->addText('reply', _('Replay to'))->setRequired(_('"Replay to" is required'))->addRule(Form::EMAIL);
        $form->addTextArea('text', _('Text'));
        $form->addSubmit('submit', _('Send'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleSimpleMailForm($form);
        };
        return $control;
    }

    /**
     * @param Form $form
     */
    private function handleCsvMailForm(Form $form) {
        $values = $form->getValues();
        $filename = $values['file']->getTemporaryFile();
        $parser = new CSVParser($filename, CSVParser::INDEX_FROM_HEADER);
        $messages = [];
        foreach ($parser as $row) {
            // todo multiple addresses
            $to = $row[$values->to];
            $messages[] = $this->composeEmail($to,
                $values->sender,
                $values->reply,
                isset($row[$values->subject]) ? $row[$values->subject] : $values->subject,
                $values->text,
                ['row' => $row],
                isset($row[$values->cc]) ? $row[$values->cc] : ($values->cc ?: null),
                isset($row[$values->bcc]) ? $row[$values->bcc] : ($values->bcc ?: null)
            );

        }
    }

    /**
     * @param string $to
     * @param string $from
     * @param string $reply
     * @param string $subject
     * @param string $text
     * @param $params
     * @param string|null $cc
     * @param string|null $bcc
     * @return Message
     */
    private function composeEmail(
        string $to,
        string $from,
        string $reply,
        string $subject,
        string $text,
        $params,
        string $cc = null,
        string $bcc = null
    ): Message {
        $message = new Message();
        $message->setSubject($subject);
        $message->addTo($to);
        if (!is_null($bcc)) {
            $message->addBcc($bcc);
        }
        if (is_null($cc)) {
            $message->addCc($cc);
        }

        $message->setFrom($from);
        $message->addReplyTo($reply);
        $message->setHtmlBody($this->createMailTemplate($text, $params));

        return $message;
    }

    /**
     * @param Form $form
     */
    private function handleSimpleMailForm(Form $form) {
        $values = $form->getValues();
        $toAddress = explode(',', $values->to);
        if (count($toAddress) > 40) {
            $this->flashMessage(_('Max 40 to address (safety limit)'), \BasePresenter::FLASH_WARNING);
            return;
        }
        $messages = [];
        foreach (explode(',', $values->to) as $to) {
            if (!$to) {
                $this->flashMessage(_('Address is empty'), \BasePresenter::FLASH_WARNING);
                continue;
            }
            $messages[] = $this->composeEmail(
                $to,
                $values->sender,
                $values->reply,
                $values->subject,
                $values->text,
                [],
                $values->cc ?: null,
                $values->bcc ?: null
            );
        }

    }

    /**
     * @param string $text
     * @param mixed $params
     * @return StreamTemplate
     */
    private function createMailTemplate(string $text, $params): StreamTemplate {
        $template = new StreamTemplate();
        $template->registerHelperLoader('Nette\Templating\Helpers::loader');
        $template->registerFilter(new Engine());
        $template->setSource($text);
        $template->params = $params;

        if ($this instanceof BasePresenter) {
            $template->baseUri = $this->getContext()->getByType(IRequest::class)->getUrl()->getBaseUrl();
        }
        return $template;
    }
}

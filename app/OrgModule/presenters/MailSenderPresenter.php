<?php


namespace OrgModule;


use FKSDB\Components\Controls\FormControl\FormControl;
use Nette\Forms\Form;
use Nette\Mail\IMailer;
use Nette\Mail\Message;

/**
 * Class MailSenderPresenter
 * @package OrgModule
 */
class MailSenderPresenter extends BasePresenter {
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

    /**
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedDefault() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('mail-send', 'default', $this->getSelectedContest()));
    }

    /**
     * @return FormControl
     * @throws \Nette\Application\BadRequestException
     */
    protected function createComponentMailForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addText('to', _('To'))->setRequired()->setOption('description', _('Comma separated address. max 40 address'));

        $form->addText('subject', _('Subject'))->setRequired(_('"Subject" is required'));

        $form->addText('bcc', _('BCC'))->addCondition(Form::FILLED)->addRule(Form::EMAIL);
        $form->addText('cc', _('CC'))->addCondition(Form::FILLED)->addRule(Form::EMAIL);
        $form->addText('from', _('From'))->setRequired(_('"From" is required'))->addRule(Form::EMAIL);
        $form->addText('replay', _('Replay to'))->setRequired(_('"Replay to" is required'))->addRule(Form::EMAIL);;
        $form->addTextArea('text', _('Text'));
        $form->addSubmit('submit', _('Send'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleForm($form);
        };
        return $control;
    }

    /**
     * @param Form $form
     */
    private function handleForm(Form $form) {
        $values = $form->getValues();
        $toAddress = \explode(',', $values->to);
        if (\count($toAddress) > 40) {
            $this->flashMessage(_('Max 40 to address (safety limit)'), \BasePresenter::FLASH_WARNING);
            return;
        }
        foreach (\explode(',', $values->to) as $to) {
            if (!$to) {
                $this->flashMessage(_('Address is empty'), \BasePresenter::FLASH_WARNING);
                continue;
            }
            $message = new Message();
            $message->setSubject($values->subject);
            if ($values->bcc) {
                $message->addBcc($values->bcc);
            }
            if ($values->cc) {
                $message->addCc($values->cc);
            }

            $message->setFrom($values->from);
            $message->addReplyTo($values->replay);
            $message->setHtmlBody($values->text);
            $message->addTo($to);
            $this->mailer->send($message);
            $this->flashMessage(\sprintf(_('%s: Message "%s" send to %s'), \date('c'), $values->subject, $to), \BasePresenter::FLASH_SUCCESS);
            \sleep(\rand(0, 2));
        }
    }
}

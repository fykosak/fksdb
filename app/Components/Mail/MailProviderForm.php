<?php

declare(strict_types=1);

namespace FKSDB\Components\Mail;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Mail\MailSource;
use FKSDB\Models\ORM\Services\EmailMessageService;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * @phpstan-template TMailTemplateParam of array
 * @phpstan-template TMailSchema of (int|bool|string)[]
 * @phpstan-type TMailSource = MailSource<TMailTemplateParam,TMailSchema>
 * @phpstan-import-type TMessageData from EmailMessageService
 */
class MailProviderForm extends BaseComponent
{
    /** @phpstan-var TMailSource */
    private MailSource $source;
    /**
     * @persistent
     * @phpstan-var  TMessageData[]|null
     */
    private ?array $previewMails = null;
    private EmailMessageService $emailMessageService;

    /**
     * @phpstan-param TMailSource $source
     */
    public function __construct(Container $container, MailSource $source)
    {
        parent::__construct($container);
        $this->source = $source;
    }

    public function inject(EmailMessageService $emailMessageService): void
    {
        $this->emailMessageService = $emailMessageService;
    }

    /**
     * @throws NotImplementedException
     */
    public function createComponentForm(): FormControl
    {
        $control = new FormControl($this->container);
        $form = $control->getForm();
        $structure = $this->source->getExpectedParams();
        foreach ($structure as $key => $item) {
            switch ($item) {
                case 'int':
                    $form->addText($key, $key)->setHtmlType('number');
                    break;
                case 'bool':
                    $form->addCheckbox($key, $key);
                    break;
                case 'string':
                    $form->addText($key, $key);
                    break;
                default:
                    throw new NotImplementedException();
            }
        }
        $form->addSubmit('preview', _('Preview'))->onClick[] =
            fn(SubmitButton $button) => $this->handlePreview($button->getForm());
        $form->addSubmit('send', _('Send'))->onClick[] =
            fn(SubmitButton $button) => $this->handleSend($button->getForm());
        return $control;
    }

    /**
     * @throws BadTypeException
     */
    private function handlePreview(Form $form): void
    {
        /** @phpstan-var TMailSchema $values */
        $values = $form->getValues('array');
        $this->previewMails = $this->source->createEmails($values);
    }

    /**
     * @throws BadTypeException
     */
    private function handleSend(Form $form): void
    {
        /** @phpstan-var TMailSchema $values */
        $values = $form->getValues('array');
        $mails = $this->source->createEmails($values);
        foreach ($mails as $mail) {
            $this->emailMessageService->addMessageToSend($mail);
        }
    }

    public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte', [
            'previewMails' => $this->previewMails,
        ]);
    }
}

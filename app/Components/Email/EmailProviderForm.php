<?php

declare(strict_types=1);

namespace FKSDB\Components\Email;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Email\UIEmailSource;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\EmailMessageService;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * @phpstan-template TEmailTemplateParam of array
 * @phpstan-template TEmailSchema of (int|bool|string)[]
 * @phpstan-type TEmailSource = UIEmailSource<TEmailTemplateParam,TEmailSchema>
 * @phpstan-import-type TMessageData from EmailMessageService
 */
class EmailProviderForm extends BaseComponent
{
    /** @phpstan-var TEmailSource */
    private UIEmailSource $source;
    /**
     * @persistent
     * @phpstan-var  TMessageData[]|null
     */
    private ?array $previewEmails = null;

    /**
     * @phpstan-param TEmailSource $source
     */
    public function __construct(Container $container, UIEmailSource $source)
    {
        parent::__construct($container);
        $this->source = $source;
    }

    public function createComponentForm(): FormControl
    {
        $control = new FormControl($this->container);
        $form = $control->getForm();
        $this->source->creatForm($form);
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
        /** @phpstan-var TEmailSchema $values */
        $values = $form->getValues('array');
        $this->previewEmails = $this->source->createEmails($values);
    }

    /**
     * @throws BadTypeException
     */
    private function handleSend(Form $form): void
    {
        /** @phpstan-var TEmailSchema $values */
        $values = $form->getValues('array');
        $this->source->createAndSend($values);
    }

    public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte', [
            'previewEmails' => $this->previewEmails,
        ]);
    }
}

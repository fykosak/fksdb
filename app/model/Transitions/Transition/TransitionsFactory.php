<?php

namespace FKSDB\Transitions;

use Mail\MailTemplateFactory;

/**
 * Class TransitionsFactory
 * @package FKSDB\Transitions
 */
class TransitionsFactory {

    /**
     * @return MailTemplateFactory
     */
    public function getMailTemplateFactory(): MailTemplateFactory {
        return $this->mailTemplateFactory;
    }

    /**
     * @var MailTemplateFactory
     */
    private $mailTemplateFactory;

    /**
     * TransitionsFactory constructor.
     * @param MailTemplateFactory $mailTemplateFactory
     */
    public function __construct(MailTemplateFactory $mailTemplateFactory) {
        $this->mailTemplateFactory = $mailTemplateFactory;
    }
}


<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Transitions;

use FKSDB\Models\Email\TemplateFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\Transitions\Holder\EmailHolder;
use FKSDB\Models\Transitions\Statement;
use Nette\DI\Container;
use Nette\InvalidStateException;

/**
 * @phpstan-implements Statement<void,EmailHolder>
 */
final class PrepareText implements Statement
{
    private EmailMessageService $emailMessageService;
    private TemplateFactory $templateFactory;
    private AuthTokenService $authTokenService;
    private LoginService $loginService;
    private Container $container;

    public function __construct(
        EmailMessageService $emailMessageService,
        TemplateFactory $templateFactory,
        AuthTokenService $authTokenService,
        LoginService $loginService,
        Container $container
    ) {
        $this->templateFactory = $templateFactory;
        $this->authTokenService = $authTokenService;
        $this->loginService = $loginService;
        $this->container = $container;
        $this->emailMessageService = $emailMessageService;
    }

    /**
     * @throws BadTypeException
     */
    public function __invoke(...$args): void
    {
        /** @var EmailHolder $holder */
        [$holder] = $args;
        $model = $holder->getModel();
        if ($model->text) {
            throw new InvalidStateException('Email has already generated text');
        }
        // add tokens if is "spam"
        if ($model->topic->isSpam()) {
            if ($model->person) {
                $login = $model->person->getLogin();
                if (!$login) {
                    $login = $this->loginService->createLogin($model->person);
                }
                $token = $this->authTokenService->createToken(
                    $login,
                    AuthTokenType::from(AuthTokenType::UNSUBSCRIBE),
                    null,
                );
            } else {
                $code = openssl_encrypt(
                    $model->recipient,
                    'aes-256-cbc',
                    $this->container->getParameters()['spamHash']
                );
                if ($code === false) {
                    throw new InvalidStateException(_('Cannot encrypt code'));
                }
            }
        }
        // finaly create template (include inner text into email with footer)
        $data = [
            'model' => $model,
            'token' => $token ?? null,
            'code' => $code ?? null,
        ];
        if ($model->topic->isSpam()) {
            $template = __DIR__ . '/../Containers/spam.latte';
        } else {
            $template = __DIR__ . '/../Containers/noSpam.latte';
        }
        $text = $this->templateFactory->create($model->lang)->renderToString($template, $data);
        $this->emailMessageService->storeModel(['text' => $text], $model);
    }
}

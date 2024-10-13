<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Transitions;

use FKSDB\Models\Email\TemplateFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\Transitions\Holder\EmailHolder;
use FKSDB\Models\Transitions\Statement;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Tracy\Debugger;

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
     * @throws \Throwable
     */
    public function __invoke(...$args): void
    {
        /** @var EmailHolder $holder */
        [$holder] = $args;
        $model = $holder->getModel();
        if ($model->text) {
            throw new InvalidStateException('Email has already generated text');
        }
        $template = $this->templateFactory->create($model->lang);
        // add tokens if is "spam"
        if ($model->topic->isSpam()) {
            if ($model->person) {
                $token = $this->authTokenService->createUnsubscribeToken(
                    $model->person->getLogin() ?? $this->loginService->createLogin($model->person)
                );
                $text = $template(
                    __DIR__ . "/../Containers/spam.person.{$model->lang->value}.latte",
                    [
                        'model' => $model,
                        'token' => $token,
                    ]
                );
            } else {
                $code = MachineCode::createStringHash(
                    $model->recipient,
                    $this->container->getParameters()['machineCode']['salt']['unsubscribe']
                );
                $text = $template(
                    __DIR__ . "/../Containers/spam.anonymous.{$model->lang->value}.latte",
                    [
                        'model' => $model,
                        'code' => $code,
                    ]
                );
            }
        } else {
            $text = $template(
                __DIR__ . '/../Containers/noSpam.latte',
                [
                    'model' => $model,
                ]
            );
        }
        $this->emailMessageService->storeModel(['text' => $text], $model);
    }
}

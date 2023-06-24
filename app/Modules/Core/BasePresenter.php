<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core;

use FKSDB\Components\Controls\Choosers\LanguageChooserComponent;
use FKSDB\Components\Controls\ColumnPrinter\ColumnRendererComponent;
use FKSDB\Components\Controls\LinkPrinter\LinkPrinterComponent;
use FKSDB\Components\Controls\Navigation\NavigationChooserComponent;
use FKSDB\Components\Controls\Navigation\PresenterBuilder;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteJSONProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\FilteredDataProvider;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\Utils\Utils;
use Fykosak\Utils\Localization\GettextTranslator;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Template;
use Nette\DI\Container;
use Nette\InvalidStateException;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter implements AutocompleteJSONProvider
{
    /**
     * @persistent
     * @internal
     */
    public ?string $lang = null;
    private string $language;
    protected ContestService $contestService;
    protected PresenterBuilder $presenterBuilder;
    protected GettextTranslator $translator;
    protected bool $authorized = true;
    private array $authorizedCache = [];
    private Container $diContainer;

    final public function injectBase(
        Container $diContainer,
        ContestService $contestService,
        PresenterBuilder $presenterBuilder,
        GettextTranslator $translator
    ): void {
        $this->contestService = $contestService;
        $this->presenterBuilder = $presenterBuilder;
        $this->translator = $translator;
        $this->diContainer = $diContainer;
    }

    /**
     * @throws BadTypeException
     */
    public function handleAutocomplete(string $acName): void
    {
        ['acQ' => $acQ] = (array)json_decode($this->getHttpRequest()->getRawBody());
        $component = $this->getComponent($acName);
        if (!$component instanceof AutocompleteSelectBox) {
            throw new BadTypeException(AutocompleteSelectBox::class, $component);
        } else {
            $provider = $component->getDataProvider();
            $data = null;
            if ($provider instanceof FilteredDataProvider) {
                $data = $provider->getFilteredItems($acQ);
            }
            $response = new JsonResponse($data);
            $this->sendResponse($response);
        }
    }

    /**
     * @throws BadRequestException
     * @throws InvalidLinkException
     */
    public function authorized(string $destination, ?array $args = null): bool
    {
        if (substr($destination, -1) === '!' || $destination === 'this') {
            $destination = $this->getAction(true);
        }

        $key = $destination . Utils::getFingerprint($args);
        if (!isset($this->authorizedCache[$key])) {
            /*
             * This part is extracted from Presenter::createRequest
             */
            $a = strrpos($destination, ':');
            if ($a === false) {
                $action = $destination;
                $presenter = $this->getName();
            } else {
                $action = (string)substr($destination, $a + 1);
                if ($destination[0] === ':') { // absolute
                    if ($a < 2) {
                        throw new InvalidLinkException("Missing presenter name in '$destination'.");
                    }
                    $presenter = substr($destination, 1, $a - 1);
                } else { // relative
                    $presenter = $this->getName();
                    $b = strrpos($presenter, ':');
                    if ($b === false) { // no module
                        $presenter = substr($destination, 0, $a);
                    } else { // with module
                        $presenter = substr($presenter, 0, $b + 1) . substr($destination, 0, $a);
                    }
                }
            }

            /*
             * Now create a mock presenter and evaluate accessibility.
             */
            $baseParams = $this->getParameters();
            $testedPresenter = $this->presenterBuilder->preparePresenter($presenter, $action, $args, $baseParams);

            try {
                $testedPresenter->checkRequirements($testedPresenter->getReflection());
                $this->authorizedCache[$key] = $testedPresenter->authorized;
            } catch (BadRequestException $exception) {
                $this->authorizedCache[$key] = false;
            }
        }
        return $this->authorizedCache[$key];
    }

    /**
     * @throws UnsupportedLanguageException
     */
    protected function startup(): void
    {
        parent::startup();
        if (!isset($this->language)) {
            $this->language = $this->selectLang();
            $this->translator->setLang($this->language);
        }
    }

    /**
     * @throws UnsupportedLanguageException
     */
    private function selectLang(): string
    {
        $candidate = $this->getUserPreferredLang() ?? $this->lang;
        $supportedLanguages = $this->translator->getSupportedLanguages();
        if (!$candidate || !in_array($candidate, $supportedLanguages)) {
            $candidate = $this->getHttpRequest()->detectLanguage($supportedLanguages);
        }
        if (!$candidate) {
            $candidate = $this->getContext()->getParameters()['localization']['defaultLanguage'];
        }
        // final check
        if (!in_array($candidate, $supportedLanguages)) {
            throw new UnsupportedLanguageException($candidate);
        }
        return $candidate;
    }

    private function getUserPreferredLang(): ?string
    {
        $person = $this->getLoggedPerson();
        if ($person) {
            return $person->getPreferredLang();
        }
        return null;
    }


    protected function getLoggedPerson(): ?PersonModel
    {
        /**@var LoginModel $login */
        $login = $this->getUser()->getIdentity();
        return $this->getUser()->isLoggedIn() ? $login->person : null;
    }

    protected function createTemplate(): Template
    {
        $template = parent::createTemplate();
        $template->setTranslator($this->translator);
        return $template;
    }

    protected function beforeRender(): void
    {
        parent::beforeRender();

        $this->template->pageTitle = $this->getTitle();
        $this->template->lang = $this->getLang();
        $this->template->navRoots = $this->getNavRoots();
        $this->template->styleId = $this->getStyleId();
    }

    public function getTitle(): PageTitle
    {
        try {
            $reflection = new \ReflectionClass($this);
            $reflectionMethod = $reflection->getMethod('title' . $this->getView());
            if ($reflectionMethod->isAbstract() || !$reflectionMethod->isPublic()) {
                throw new InvalidStateException(
                    sprintf(
                        'Method %s of %s should be public and not abstract.',
                        $reflectionMethod->getName(),
                        get_class($this)
                    )
                );
            }
            $pageTitle = $reflectionMethod->invoke($this);
            $pageTitle->subTitle = $this->getSubTitle();
        } catch (\ReflectionException$exception) {
            throw new InvalidStateException(
                sprintf('Missing or invalid %s method in %s', 'title' . $this->getView(), $reflection->getName())
            );
        }
        return $pageTitle;
    }

    protected function getSubTitle(): ?string
    {
        return null;
    }

    protected function getStyleId(): string
    {
        return 'default';
    }

    public function getLang(): string
    {
        return $this->language;
    }

    protected function getNavRoots(): array
    {
        return [];
    }

    public function getContext(): Container
    {
        return $this->diContainer;
    }

    protected function createComponentNavigationChooser(): NavigationChooserComponent
    {
        return new NavigationChooserComponent($this->getContext());
    }

    protected function createComponentValuePrinter(): ColumnRendererComponent
    {
        return new ColumnRendererComponent($this->getContext());
    }

    protected function createComponentLinkPrinter(): LinkPrinterComponent
    {
        return new LinkPrinterComponent($this->getContext());
    }

    final protected function createComponentLanguageChooser(): LanguageChooserComponent
    {
        return new LanguageChooserComponent($this->getContext(), $this->language, !$this->getUserPreferredLang());
    }
}

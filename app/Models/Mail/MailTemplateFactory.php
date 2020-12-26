<?php

namespace FKSDB\Models\Mail;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Localization\UnsupportedLanguageException;
use FKSDB\Modules\Core\BasePresenter;
use Nette\Application\Application;
use Nette\Application\UI\ITemplate;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Http\IRequest;
use Nette\InvalidArgumentException;
use Nette\Localization\ITranslator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @author Michal Cervenak <miso@fykos.cz>
 */
class MailTemplateFactory {

    /** without trailing slash */
    private string $templateDir;
    /** @var Application */
    private $application;

    private ITranslator $translator;

    private IRequest $request;

    public function __construct(string $templateDir, Application $application, ITranslator $translator, IRequest $request) {
        $this->templateDir = $templateDir;
        $this->application = $application;
        $this->translator = $translator;
        $this->request = $request;
    }

    /**
     * @param Application $application
     * @internal For automated testing only.
     * @deprecated
     * TODO remove this!
     */
    final public function injectApplication($application): void {
        $this->application = $application;
    }

    /**
     * @param string|null $lang ISO 639-1
     * @param array $data
     * @return ITemplate
     * @throws BadTypeException
     * @throws UnsupportedLanguageException
     */
    public function createLoginInvitation(?string $lang, array $data): ITemplate {
        return $this->createWithParameters('loginInvitation', $lang, $data);
    }

    /**
     * @param string|null $lang ISO 639-1
     * @param array $data
     * @return ITemplate
     * @throws BadTypeException
     * @throws UnsupportedLanguageException
     */
    public function createPasswordRecovery(?string $lang, array $data): ITemplate {
        return $this->createWithParameters('passwordRecovery', $lang, $data);
    }

    /**
     * @param string $templateFile
     * @param string|null $lang ISO 639-1
     * @param array $data
     * @return ITemplate
     * @throws BadTypeException
     * @throws UnsupportedLanguageException
     */
    public function createWithParameters(string $templateFile, ?string $lang, array $data = []): ITemplate {
        $template = $this->createFromFile($templateFile, $lang);
        $template->setTranslator($this->translator);
        foreach ($data as $key => $value) {
            $template->{$key} = $value;
        }
        return $template;
    }

    /**
     * @param string $filename
     * @param string|null $lang ISO 639-1
     * @return ITemplate
     * @throws UnsupportedLanguageException
     * @throws BadTypeException
     */
    final public function createFromFile(string $filename, ?string $lang): ITemplate {
        $presenter = $this->application->getPresenter();
        if (($lang === null) && !$presenter instanceof BasePresenter) {
            throw new BadTypeException(BasePresenter::class, $presenter);
        }
        if ($lang === null) {
            $lang = $presenter->getLang();
        }
        $control = $presenter;

        $file = $this->templateDir . DIRECTORY_SEPARATOR . "$filename.$lang.latte";
        if (!file_exists($file)) {
            throw new InvalidArgumentException("Cannot find template '$filename.$lang'.");
        }
        $template = $presenter->getTemplateFactory()->createTemplate();
        $template->setFile($file);

        if ($template instanceof Template) {
            $template->getLatte()->addProvider('uiControl', $control);
        }
        $template->control = $control;
        $template->baseUri = $this->request->getUrl()->getBaseUrl();
        return $template;
    }
}

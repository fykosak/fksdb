<?php

namespace FKSDB\Localization;

use Nette\InvalidArgumentException;
use Nette\Localization\ITranslator;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class GettextTranslator implements ITranslator {

    /** @var array[lang] => locale */
    private $locales;

    /** @var string */
    private $localeDir;
    /** @var string */
    private $lang;

    /**
     * GettextTranslator constructor.
     * @param array $locales
     * @param string $localeDir
     */
    public function __construct(array $locales, string $localeDir) {
        $this->locales = $locales;
        $this->localeDir = $localeDir;
    }

    public function getLang(): ?string {
        return $this->lang;
    }

    /**
     *
     * @param string $lang ISO 639-1
     */
    public function setLang(string $lang): void {
        if (!isset($this->locales[$lang])) {
            throw new InvalidArgumentException("Language $lang not supported");
        }
        $this->lang = $lang;
        $locale = $this->locales[$lang];

        putenv("LANGUAGE=$locale"); // for the sake of CLI tests
        setlocale(LC_MESSAGES, $locale);
        bindtextdomain('messages', $this->localeDir);
        bind_textdomain_codeset('messages', "utf-8");
        textdomain('messages');
    }

    /**
     * @return string[]
     */
    public function getSupportedLanguages(): array {
        return array_keys($this->locales);
    }

    /**
     * @param mixed $message
     * @param mixed ...$parameters
     * @return string
     */
    public function translate($message, ...$parameters): string {
        if ($message === "" || $message === null) {
            return "";
        }
        [$count] = $parameters;
        if ($count !== null) {
            return ngettext($message, $message, (int)$count);
        } else {
            return gettext($message);
        }
    }

    /**
     * @param object $object
     * @param string $field
     * @param string $lang
     * @return mixed
     */
    public static function i18nHelper($object, $field, $lang) {
        return $object->{$field . '_' . $lang};
    }

}

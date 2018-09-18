<?php

namespace FKSDB\Localization;

use Nette\InvalidArgumentException;
use Nette\Localization\ITranslator;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class GettextTranslator implements ITranslator {

    /**
     * @var array[lang] => locale
     */
    private $locales = array();

    /**
     * @var string
     */
    private $localeDir;
    private $lang;

    function __construct(array $locales, $localeDir) {
        $this->locales = $locales;
        $this->localeDir = $localeDir;
    }

    public function getLang() {
        return $this->lang;
    }

    /**
     *
     * @param string $lang ISO 639-1
     */
    public function setLang($lang) {
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

    public function getSupportedLanguages() {
        return array_keys($this->locales);
    }

    public function translate($message, $count = NULL) {
        if ($message === "" || $message === null) {
            return "";
        }
        if ($count !== null) {
            return ngettext($message, $message, (int) $count);
        } else {
            return gettext($message);
        }
    }

    public static function i18nHelper($object, $field, $lang) {
        return $object->{$field . '_' . $lang};
    }

}

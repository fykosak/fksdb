import { data } from './i18n-data';

export type LangMap<TValue> = {
    [key in availableLanguage]: TValue;
};

type LanguageData = LangMap<{
    [msqId: string]: string;
}>;

export type availableLanguage = 'cs' | 'en' | 'sk';

class Lang {

    private readonly data: LanguageData = {cs: {}, en: {}, sk: {}};

    private currentLocale: availableLanguage = 'cs';

    public constructor(langData: LanguageData) {
        this.data = langData;
        window.location.search.slice(1).split('&').forEach((s) => {
            const [key, value] = s.split('=');
            if (key === 'lang') {
                this.setLocale(value as availableLanguage);
            }
        });
    }

    public getCurrentLocale(): availableLanguage {
        return this.currentLocale;
    }

    public setLocale(locale: availableLanguage): void {
        this.currentLocale = locale;
    }

    public getAvailableLocales(): string[] {
        return Object.keys(this.data);
    }

    public getText(msgId: string): string {
        if (this.data[this.currentLocale].hasOwnProperty(msgId) && this.data[this.currentLocale][msgId]) {
            return this.data[this.currentLocale][msgId];
        }
        return msgId;
    }

    public getLocalizedText(msgId: string, locale: availableLanguage): string {
        if (this.data[locale].hasOwnProperty(msgId) && this.data[locale][msgId]) {
            return this.data[locale][msgId];
        }
        return msgId;
    }

    public getBCP47() {
        switch (this.currentLocale) {
            case 'cs':
                return 'cs-CZ';
            case 'en':
                return 'en-GB';
        }
    }
}

export const lang = new Lang(data);

export type LocalizedString = {
    [key in availableLanguage]: string;
};

import { data } from '../../../i18n/i18n-data';

export type LangMap<TValue, Lang extends string> = {
    [key in Lang]: TValue;
};

type LanguageData<Lang extends string> = LangMap<{
    [msqId: string]: string;
}, Lang>;

export type availableLanguage = 'cs' | 'en';

export class Translator<Lang extends string> {

    private readonly data: LanguageData<Lang>;
    private readonly currentLocale: Lang = 'cs' as Lang;

    public constructor() {
        // @ts-ignore
        this.data = data;
        const el = document.getElementsByClassName('html').item(0);
        if (el) {
            const lang = el.getAttribute('lang');
            this.currentLocale = lang as Lang;
        }
    }

    public getText(msgId: string): string {
        if (Object.hasOwn(this.data[this.currentLocale], msgId) && this.data[this.currentLocale][msgId]) {
            return this.data[this.currentLocale][msgId];
        }
        return msgId;
    }

    public getLocalizedText(msgId: string, locale: Lang): string {
        if (Object.hasOwn(this.data[locale], msgId) && this.data[locale][msgId]) {
            return this.data[locale][msgId];
        }
        return msgId;
    }

    public getBCP47(): string {
        switch (this.currentLocale) {
            case 'cs':
                return 'cs-CZ';
            case 'en':
                return 'en-GB';
        }
    }

    public get<T>(map: LangMap<T, Lang>): T {
        return map[this.currentLocale];
    }
}

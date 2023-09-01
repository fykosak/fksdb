import { data } from '../../../i18n/i18n-data';

export type LangMap<TValue, Lang extends string> = {
    [key in Lang]: TValue;
};

type LanguageData<Lang extends string> = LangMap<{
    [msqId: string]: string[];
}, Lang>;

export class Translator<Lang extends string = 'cs' | 'en'> {

    private readonly data: LanguageData<Lang>;
    private readonly currentLocale: Lang = 'cs' as Lang;

    public constructor() {
        this.data = data as LanguageData<Lang>;
        const el = document.getElementsByTagName('html').item(0);
        if (el) {
            const lang = el.getAttribute('lang');
            this.currentLocale = lang as Lang;
        }
    }

    public getText(msgId: string): string {
        if (Object.hasOwn(this.data[this.currentLocale], msgId) && this.data[this.currentLocale][msgId]) {
            return this.data[this.currentLocale][msgId][0];
        }
        return msgId;
    }

    private getPluralFormId(count: number): number {
        if (count == 1 || count == -1) {
            return 0;
        }
        if ((count >= 2 && count <= 4) || (count >= -4 && count <= -2)) {
            return 1;
        }
        return 2;
    }

    public nGetText(msgId: string, msgIdPlural: string, count: number): string {
        const formId: number = this.getPluralFormId(count);
        if (Object.hasOwn(this.data[this.currentLocale], msgId) && this.data[this.currentLocale][msgId] &&
            this.data[this.currentLocale][msgId].length <= formId && this.data[this.currentLocale][msgId][formId]) {
            return this.data[this.currentLocale][msgId][formId];
        }

        if (formId == 0) {
            return msgId;
        }

        return msgIdPlural;
    }

    public getLocalizedText(msgId: string, locale: Lang): string {
        if (Object.hasOwn(this.data[locale], msgId) && this.data[locale][msgId]) {
            return this.data[locale][msgId][0];
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

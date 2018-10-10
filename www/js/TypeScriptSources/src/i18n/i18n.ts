import { data } from './i18n-data';

interface ILanguageData {
    [lang: string]: {
        [msqId: string]: string;
    };
}

class Lang {

    private readonly data: ILanguageData = {};

    private currentLocale: string = 'cs';

    public constructor(langData: ILanguageData) {
        this.data = langData;
    }

    public getCurrentLocale(): string {
        return this.currentLocale;
    }

    public setLocale(locale: string): void {
        this.currentLocale = locale;
    }

    public getAvailableLocales(): string[] {
        return Object.keys(this.data);
    }

    public getText(msgId: string): string {
        if (this.data[this.currentLocale].hasOwnProperty(msgId)) {
            return this.data[this.currentLocale][msgId];
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

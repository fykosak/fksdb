import { data } from './i18n-data';

interface ILanguageData {
    [lang: string]: {
        [msqId: string]: string;
    };
}

class Lang {

    private readonly data: ILanguageData = {};

    private currentLocale = 'cs';

    public constructor(langData: ILanguageData) {
        this.data = langData;
        window.location.search.slice(1).split('&').forEach((s) => {
            const [key, value] = s.split('=');
            if (key === 'lang') {
                this.currentLocale = value;
            }
        });
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
        if (this.data[this.currentLocale].hasOwnProperty(msgId) && this.data[this.currentLocale][msgId]) {
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

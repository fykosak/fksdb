interface ITranslation {
    cs: string;
    en: string;
}

class Lang {

    private translations: { [key: string]: ITranslation } = {
        bod: {cs: 'bod', en: 'point'},
        body: {cs: 'body', en: 'points'},
        bodů: {cs: 'bodů', en: 'points'},
        filters: {
            cs: 'Filtre',
            en: 'Filters',
        },
        lastUpdated: {
            cs: 'Nasposledy obnoveno',
            en: 'Last updated',
        },
        options: {
            cs: 'Nastavení',
            en: 'Options',
        },
        physicsBrawl: {
            cs: 'FYKOSí Fyziklání',
            en: 'Physics Brawl',
        },
        statistics: {
            cs: 'Štatistiky',
            en: 'Statistics',
        },
        successOfSubmitting: {
            cs: 'Úspešnosť odovzdávania úloh',
            en: 'Success of submitting tasks',
        },
        table: {
            cs: 'Tabulka',
            en: 'Table',
        },
        tasksStatistics: {
            cs: 'Štatistika úloh',
            en: 'Statistics of tasks',
        },
        teamStatistics: {
            cs: 'Štatistika týmu',
            en: 'Statistics of team',
        },
        timeLine: {
            cs: 'Časová os',
            en: 'Timeline',
        },
        timeProgress: {
            cs: 'Časový vývoj',
            en: 'Time progress',
        },
    };
    private language = 'cs';

    public constructor() {

        window.location.search.substring(1).split('&').forEach((subString) => {
            const [key, value] = subString.split('=');
            if (key === 'lang') {
                this.language = value;
            }
        });
        console.log(this.language);
    }

    public getLang(key: string): string {
        if (this.translations.hasOwnProperty(key)) {
            return this.translations[key][this.language];
        }
        console.log('language key ' + key + ' not found');
    }
}

export const lang = new Lang();

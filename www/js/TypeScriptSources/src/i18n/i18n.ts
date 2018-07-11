import {data} from "./i18n-data.ts"

let currentLocale = 'cs';

export function getCurrentLocale() {
    return locale;
}

export function setLocale(locale) {
    currentLocale = locale;
}

export function getAvailableLocales() {
    return Object.keys(data);
}

export function gettext(msgid) {
    if (data[currentLocale].hasOwnProperty(msgid)) {
        return data[currentLocale][msgid];
    }
    return msgid;
}

import { LangMap } from '@translator/translator';

export interface EventModel {
    eventId: number;
    year: number;
    eventYear: number;
    begin: string;
    end: string;
    registrationBegin: string;
    registrationEnd: string;
    name: LangMap<'cs' | 'en', string>;
    report: LangMap<'cs' | 'en', string>;
    description: LangMap<'cs' | 'en', string>;
    eventTypeId: number;
}

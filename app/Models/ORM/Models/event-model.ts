import { LangMap } from '@translator/translator';

export interface EventModel {
    eventId: number;
    year: number;
    eventYear: number;
    begin: string;
    end: string;
    registrationBegin: string;
    registrationEnd: string;
    nameNew: LangMap<string, 'cs' | 'en'>;
    reportNew: LangMap<string, 'cs' | 'en'>;
    description: LangMap<string, 'cs' | 'en'>;
    eventTypeId: number;
}

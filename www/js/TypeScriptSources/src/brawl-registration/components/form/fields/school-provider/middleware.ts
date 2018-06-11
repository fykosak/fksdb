import { netteFetch } from '../../../../../fetch-api/middleware/fetch';
import {
    IRequestData,
    ISchool,
} from './interfaces';

export const loadOptions = (payload, cb) => {
    const netteJQuery: any = $;
    netteJQuery.nette.ext('unique', null);
    return netteFetch<IRequestData, ISchool[]>({
        act: 'school-provider',
        data: {
            payload,
        },
    }, (response) => {
        cb(null, {
            complete: true,
            options: response.data,
        });
    }, (e) => {
        throw e;
    });
};

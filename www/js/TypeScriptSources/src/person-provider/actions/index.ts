import { Dispatch } from 'redux';
import { dispatchNetteFetch } from '../../fetch-api/middleware/fetch';
import {
    IRequestData,
    IResponseData,
} from '../interfaces';
import { IStore } from '../reducers';

export const ACTION_CLEAR_PROVIDER_PROPERTY = '@@person-provider/CLEAR_PROVIDER_PROPERTY';

export const clearProviderProviderProperty = (selector: string, property: string) => {
    return {
        property,
        selector,
        type: ACTION_CLEAR_PROVIDER_PROPERTY,
    };
};

export const ACTION_CHANGE_EMAIL_VALUE = 'ACTION_CHANGE_EMAIL_VALUE';

export const changeEmailValue = (value: string) => {
    return {
        type: ACTION_CHANGE_EMAIL_VALUE,
        value,
    };
};

export const findButtonClick = (dispatch: Dispatch<IStore>, value: string, accessKey: string) => {

    return dispatchNetteFetch<IRequestData, IResponseData, IStore>('personProvider/' + accessKey, dispatch, {
        act: 'person-provider',
        data: {
            accessKey,
            email: value,
            fields: [],
        },
    }, () => null, () => null);
};

import { ACTION_SUBMIT_SUCCESS } from '../../fetch-api/actions/submit';
import { ACTION_CLEAR_PROVIDER_PROPERTY } from '../actions';
import { IProviderValue } from '../interfaces';
import { getAccessKey } from '../validation';

const providerLoadData = (state: IProviderStore, action): IProviderStore => {
    // TODO catch only provider request
    if (action.data.act !== 'person-provider') {
        return state;
    }
    const {data: {data: {key, fields}}} = action;
    const personState = {};
    for (const field in fields) {
        if (fields.hasOwnProperty(field)) {
            personState[getAccessKey(key, field)] = action.data.data.fields[field];
        }
    }
    return {
        ...state,
        [action.data.data.key]: {
            fields: personState,
            isServed: true,
        },
    };

};

const clearProperty = (state: IProviderStore, action): IProviderStore => {
    const {selector, property} = action;
    return {
        ...state,
        [selector]: {
            ...state[selector],
            fields: {
                ...state[selector].fields,
                [property]: {
                    ...state[selector].fields[property],
                    hasValue: false,
                },
            },
        },
    };
};

export const provider = (state: IProviderStore = {}, event): IProviderStore => {
    switch (event.type) {
        case ACTION_SUBMIT_SUCCESS:
            return providerLoadData(state, event);
        case ACTION_CLEAR_PROVIDER_PROPERTY:
            return clearProperty(state, event);
        default:
            return state;

    }
};

export interface IProviderStore {
    [accessKey: string]: {
        isServed: boolean;
        fields: {
            [value: string]: IProviderValue<any>;
        };
    };
}

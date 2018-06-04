import { ACTION_SUBMIT_SUCCESS } from '../../fetch-api/actions/submit';
import { ACTION_CLEAR_PROVIDER_PROPERTY } from '../actions';
import { IProviderValue } from '../interfaces';

const providerLoadData = (state: IProviderStore, event): IProviderStore => {
    // TODO catch only provider request
    if (event.data.act !== 'person-provider') {
        return state;
    }
    return {
        ...state,
        [event.data.data.key]: {
            ...state[event.data.data.key],
            fields: event.data.data.fields,
        },
    };

};

const clearProperty = (state: IProviderStore, action): IProviderStore => {
    const [prefix, property] = action.selector.split('.');

    return {
        ...state,
        [prefix]: {
            ...state[prefix],
            [property]: {
                ...state[prefix][property],
                hasValue: false,
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
        fields: {
            [value: string]: IProviderValue<any>;
        };
    };
}

import { ACTION_SUBMIT_SUCCESS } from '../../submit/actions/submit';
import { ACTION_CLEAR_PROVIDER_PROPERTY } from '../actions';

const providerLoadData = (state: IProviderStore, event): IProviderStore => {
    // TODO catch only provider request
    if (event.data.act !== 'person-provider') {
        return state;
    }
    return {
        ...state,
        [event.data.key]: event.data.data.fields,
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

export interface IProviderValue {
    value: any;
    hasValue: boolean;
}

export interface IProviderStore {
    [accessKey: string]: {
        [value: string]: IProviderValue;
    };
}

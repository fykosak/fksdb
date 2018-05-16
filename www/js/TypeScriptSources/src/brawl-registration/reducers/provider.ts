import { ACTION_SUBMIT_SUCCESS } from '../../entry-form/actions';
import { ACTION_CLEAR_PRIVIDER_PROPERTY } from '../actions/load';

const providerLoadData = (state, event) => {
    return {
        ...state,
        [event.data.key]: event.data.fields,
    };

};

const clearProperty = (state, action) => {
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

export const provider = (state = {}, event) => {
    switch (event.type) {
        // case ACTION_PROVIDER_LOAD_DATA:
        case ACTION_SUBMIT_SUCCESS:
            return providerLoadData(state, event);
        case ACTION_CLEAR_PRIVIDER_PROPERTY:
            return clearProperty(state, event);
        default:
            return state;

    }

};

import { ACTION_SUBMIT_SUCCESS } from '../../entry-form/actions';

const providerLoadData = (state, event) => {
    return {
        ...state,
        [event.data.key]: event.data.fields,
    };

};

export const provider = (state = {}, event) => {
    switch (event.type) {
        // case ACTION_PROVIDER_LOAD_DATA:
        case ACTION_SUBMIT_SUCCESS:
            return providerLoadData(state, event);
        default:
            return state;

    }

};

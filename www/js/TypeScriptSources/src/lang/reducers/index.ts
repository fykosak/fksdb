import { ACTION_SUBMIT_SUCCESS } from '../../submit/actions/submit';
import { AJAX_CALL_ACTION } from '../constants';

const loadData = (state: ILangStore, action): ILangStore => {
    const {data} = action;
    if (data.act !== AJAX_CALL_ACTION) {
        return state;
    }
    return {
        ...state,
        data: {...data.data},
        isReady: true,
    };
};

const initialState = {
    data: {},
    isReady: false,
    lang: 'cs',
};

export const lang = (state: ILangStore = initialState, event): ILangStore => {
    switch (event.type) {
        case ACTION_SUBMIT_SUCCESS:
            return loadData(state, event);
        default:
            return state;
    }
};

export interface ILangStore {
    isReady: boolean;
    lang: string;
    data: {
        [key: string]: string;
    };
}

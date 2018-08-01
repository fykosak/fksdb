import {
    ACTION_CLEAR_HTML_FORM,
    ACTION_SET_HTML_FORM,
    ACTION_TOGGLE_HTML_FORM,
} from '../actions/external-form';

export interface IExternalHtmlState {
    html: string;
    display: boolean;
}

const setHtml = (state: IExternalHtmlState, action): IExternalHtmlState => {
    const {html} = action;
    return {
        ...state,
        html,
    };
};

const clearHtml = (state: IExternalHtmlState): IExternalHtmlState => {
    return {
        ...state,
        html: null,
    };
};

const toggleForm = (state: IExternalHtmlState): IExternalHtmlState => {
    return {
        ...state,
        display: !state.display,
    };
};

export const externalForm = (state: IExternalHtmlState = {html: null, display: true}, event): IExternalHtmlState => {
    switch (event.type) {
        case ACTION_SET_HTML_FORM:
            return setHtml(state, event);
        case ACTION_CLEAR_HTML_FORM:
            return clearHtml(state);
        case ACTION_TOGGLE_HTML_FORM:
            return toggleForm(state);
        default:
            return state;
    }
};

export const ACTION_CLEAR_HTML_FORM = 'ACTION_CLEAR_HTML_FORM';

export const clearHtml = () => {
    return {
        type: ACTION_CLEAR_HTML_FORM,
    };
};

export const ACTION_SET_HTML_FORM = 'ACTION_SET_HTML_FORM';

export const setHtml = (html: string) => {
    return {
        html,
        type: ACTION_SET_HTML_FORM,
    };
};

export const ACTION_TOGGLE_HTML_FORM = 'ACTION_TOGGLE_HTML_FORM';

export const toggleForm = () => {
    return {
        type: ACTION_TOGGLE_HTML_FORM,
    };
};

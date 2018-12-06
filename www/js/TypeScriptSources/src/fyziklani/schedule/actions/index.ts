export const ACTION_TOGGLE_CHOOSER = 'ACTION_TOGGLE_CHOOSER';
export const toggleChooser = () => {
    return {
        type: ACTION_TOGGLE_CHOOSER,
    };
};

export const ACTION_SET_VISIBILITY = 'ACTION_SET_VISIBILITY';

export const setVisibility = (state: boolean) => {
    return {
        state,
        type: ACTION_SET_VISIBILITY,
    };
};

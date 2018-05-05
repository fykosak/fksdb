export const ACTION_ADD_ERROR = 'ACTION_ADD_ERROR';

export const addError = (error) => {
    return {
        error,
        type: ACTION_ADD_ERROR,
    };
};

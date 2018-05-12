export const ACTION_LOAD_DEFAULT_DATA = 'ACTION_LOAD_DEFAULT_DATA';

export const loadData = (data) => {
    return {
        data,
        type: ACTION_LOAD_DEFAULT_DATA,
    };
};

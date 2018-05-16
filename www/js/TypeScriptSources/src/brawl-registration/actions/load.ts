export const ACTION_LOAD_DEFAULT_DATA = 'ACTION_LOAD_DEFAULT_DATA';

export const loadData = (data) => {
    return {
        data,
        type: ACTION_LOAD_DEFAULT_DATA,
    };
};

export const ACTION_CLEAR_PRIVIDER_PROPERTY = 'ACTION_CLEAR_PRIVIDER_PROPERTY';

export const clearProviderProviderProperty = (selector: string) => {
    return {
        selector,
        type: ACTION_CLEAR_PRIVIDER_PROPERTY,
    };
};

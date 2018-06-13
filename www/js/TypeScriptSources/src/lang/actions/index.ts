export const ACTION_LOAD_LANG = 'ACTION_LOAD_LANG';
export const loadLang = (data) => {
    return {
        data,
        type: ACTION_LOAD_LANG,
    };
};

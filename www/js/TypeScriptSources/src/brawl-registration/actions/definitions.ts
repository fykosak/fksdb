export const ACTION_SET_DEFINITIONS = 'ACTION_SET_DEFINITIONS';

export const setDefinitions = (defs: any) => {
    return {
        data: defs,
        type: ACTION_SET_DEFINITIONS,
    };
};

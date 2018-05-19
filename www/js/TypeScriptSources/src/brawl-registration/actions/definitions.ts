import { IDefinitionsState } from '../reducers/definitions';

export const ACTION_SET_DEFINITIONS = 'ACTION_SET_DEFINITIONS';

export const setDefinitions = (definitions: IDefinitionsState) => {
    return {
        data: definitions,
        type: ACTION_SET_DEFINITIONS,
    };
};

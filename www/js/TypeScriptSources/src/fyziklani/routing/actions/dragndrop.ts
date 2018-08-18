import { Action } from 'redux';
import { IPlace } from '../../helpers/interfaces';

export const ACTION_DRAG_START = 'ACTION_DRAG_START';

export const dragStart = (teamId: number) => {
    return {
        teamId,
        type: ACTION_DRAG_START,
    };
};

export const ACTION_DRAG_END = 'ACTION_DRAG_END';

export const dragEnd = (): Action => {
    return {
        type: ACTION_DRAG_END,
    };
};

export const ACTION_DROP_ITEM = 'ACTION_DROP_ITEM';

export const dropItem = (teamId: number, place: IPlace) => {
    return {
        place,
        teamId,
        type: ACTION_DROP_ITEM,
    };
};

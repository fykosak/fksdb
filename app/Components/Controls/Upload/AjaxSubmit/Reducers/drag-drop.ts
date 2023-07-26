import {
    ACTION_DRAG_END,
    ACTION_DRAG_START,
    ACTION_DROP_ITEM,
    ACTION_STATE,
} from 'FKSDB/Models/FrontEnd/shared/dragndrop';

export const dragDrop = (state = false, action): boolean => {
    switch (action.type) {
        case ACTION_STATE:
            return true;
        case ACTION_DRAG_END:
        case ACTION_DROP_ITEM:
            return false;
        default:
            return state;
    }
};

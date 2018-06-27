import { NEW_DATA_ARRIVED } from '../actions/downloader';
import {
    CHANGE_PAGE,
    CHANGE_SUBPAGE,
    SET_HARD_VISIBLE,
} from '../actions/options';
import { SET_USER_FILTER } from '../actions/table-filter';

export interface IState {
    isReady?: boolean;
    hardVisible?: boolean;
    isOrg?: boolean;
    subPage?: string;
    page?: string;
}

const setHardVisible = (state: IState, action): IState => {
    return {
        ...state,
        hardVisible: action.status,
    };
};

const setStatuses = (state: IState, action): IState => {
    return {
        ...state,
        isOrg: action.orgStatus,
        isReady: action.readyStatus,
    };
};

const changeSubPage = (state: IState, action): IState => {
    const {subPage, page} = action;
    return {
        ...state,
        page,
        subPage,
    };
};

const changePage = (state: IState, action): IState => {
    const {page} = action;
    return {
        ...state,
        page,
    };
};

export const options = (state: IState = {page: 'table'}, action): IState => {
    switch (action.type) {
        case SET_HARD_VISIBLE:
            return setHardVisible(state, action);
        case NEW_DATA_ARRIVED:
            return setStatuses(state, action);
        case CHANGE_SUBPAGE:
            return changeSubPage(state, action);
        case CHANGE_PAGE:
        case SET_USER_FILTER:
            return changePage(state, action);
        default:
            return state;
    }
};

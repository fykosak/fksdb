import {
    CHANGE_PAGE,
    CHANGE_SUBPAGE,
    SET_HARD_VISIBLE,
    SET_ORG_STATUS,
    SET_READY_STATUS
} from '../actions/options';
export interface IState {
    isReady?: boolean;
    hardVisible?: boolean;
    isOrg?: boolean;
    subPage?: string;
    page?: string;
}

const setReadyStatus = (state: IState, action): IState => {
    return {
        ...state,
        isReady: action.status,
    };
};

const setHardVisible = (state: IState, action): IState => {
    return {
        ...state,
        hardVisible: action.status,
    };
};

const setOrgStatus = (state: IState, action): IState => {
    return {
        ...state,
        isOrg: action.status,
    };
};

const changeSubPage = (state: IState, action): IState => {
    const { subPage } = action;
    return {
        ...state,
        subPage,
    };
};

const changePage = (state: IState, action): IState => {
    const { page } = action;
    return {
        ...state,
        page,
    };
};

export const options = (state: IState = {}, action): IState => {
    switch (action.type) {
        case SET_READY_STATUS:
            return setReadyStatus(state, action);
        case SET_HARD_VISIBLE:
            return setHardVisible(state, action);
        case SET_ORG_STATUS:
            return setOrgStatus(state, action);
        case CHANGE_SUBPAGE:
            return changeSubPage(state, action);
        case CHANGE_PAGE:
            return changePage(state, action);
        default:
            return state;
    }
};

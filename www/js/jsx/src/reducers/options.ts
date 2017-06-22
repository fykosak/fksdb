import {
    SET_READY_STATUS,
    SET_HARD_VISIBLE,
    SET_ORG_STATUS,
    CHANGE_SUBPAGE,
    CHANGE_PAGE,
} from '../actions/options';

const setReadyStatus = (state, action) => {
    return {
        ...state,
        isReady: action.status,
    };
};

const setHardVisible = (state, action) => {
    return {
        ...state,
        hardVisible: action.status,
    };
};

const setOrgStatus = (state, action) => {
    return {
        ...state,
        isOrg: action.status,
    };
};

const changeSubPage = (state, action) => {
    const {subPage} = action;
    return {
        ...state,
        subPage,
    };
};

const changePage = (state, action) => {
    const {page} = action;
    return {
        ...state,
        page,
    };
};

export const options = (state = {}, action) => {
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

export const SET_READY_STATUS = 'SET_READY_STATUS';

export const setReadyStatus = (status: boolean) => {
    return {
        status,
        type: SET_READY_STATUS,
    };
};

export const SET_HARD_VISIBLE = 'SET_HARD_VISIBLE';

export const setHardVisible = (status) => {
    return {
        status,
        type: SET_HARD_VISIBLE,
    };
};

export const SET_ORG_STATUS = 'SET_ORG_STATUS';

export const setOrgStatus = (status) => {
    return {
        status,
        type: SET_ORG_STATUS,
    };
};

export const CHANGE_PAGE = 'CHANGE_PAGE';

export const changePage = (page) => {
    return {
        page,
        type: CHANGE_PAGE,
    };
};

export const CHANGE_SUBPAGE = 'CHANGE_SUBPAGE';

export const changeSubPage = (subPage) => {
    return {
        subPage,
        type: CHANGE_SUBPAGE,
    };
};

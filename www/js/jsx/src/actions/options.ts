export const SET_READY_STATUS = 'SET_READY_STATUS';

export const setReadyStatus = (status: boolean) => {
    return {
        type: SET_READY_STATUS,
        status,
    };
};

export const SET_HARD_VISIBLE = 'SET_HARD_VISIBLE';

export const setHardVisible = (status) => {
    return {
        type: SET_HARD_VISIBLE,
        status,
    };
};

export const SET_ORG_STATUS = 'SET_ORG_STATUS';

export const setOrgStatus = (status) => {
    return {
        type: SET_ORG_STATUS,
        status,
    };
};

export const CHANGE_PAGE = 'CHANGE_PAGE';

export const changePage = (page) => {
    return {
        type: CHANGE_PAGE,
        page,
    };
};

export const CHANGE_SUBPAGE = 'CHANGE_SUBPAGE';

export const changeSubPage = (subPage) => {
    return {
        type: CHANGE_SUBPAGE,
        subPage,
    };
};

export const CHANGE_FILTER = 'CHANGE_FILTER';

export const changeFilter = (filter) => {
    return {
        type: CHANGE_FILTER,
        filter,
    };
};

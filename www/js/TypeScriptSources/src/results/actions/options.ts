
export const SET_HARD_VISIBLE = 'SET_HARD_VISIBLE';

export const setHardVisible = (status: boolean) => {
    return {
        status,
        type: SET_HARD_VISIBLE,
    };
};

export const CHANGE_PAGE = 'CHANGE_PAGE';

export const changePage = (page: string) => {
    return {
        page,
        type: CHANGE_PAGE,
    };
};

export const CHANGE_SUBPAGE = 'CHANGE_SUBPAGE';

export const changeSubPage = (page: string, subPage: string) => {
    return {
        page,
        subPage,
        type: CHANGE_SUBPAGE,
    };
};

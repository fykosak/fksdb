export const SET_READY_STATUS = 'SET_READY_STATUS';

export const setReadyStatus = (status: boolean) => {
    return {
        type: SET_READY_STATUS,
        status,
    };
};

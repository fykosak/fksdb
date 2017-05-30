export const UPDATE_TIMES = 'UPDATE_TIMES';

export const updateTimes = (times: any) => {
    return {
        type: UPDATE_TIMES,
        times,
    };
};

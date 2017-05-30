export const UPDATE_TIMES = 'UPDATE_TIMES';

export const updateTimes = (times: any) => {
    const {toStart, toEnd} = times;
    return {
        type: UPDATE_TIMES,
        times: {
            ...times,
            toStart: toStart * 1000,
            toEnd: toEnd * 1000,
        },
    };
};

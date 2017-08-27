export const UPDATE_TIMES = 'UPDATE_TIMES';

export const updateTimes = (times: any) => {
    const { toStart, toEnd } = times;
    return {
        times: {
            ...times,
            toEnd: toEnd * 1000,
            toStart: toStart * 1000,
        },
        type: UPDATE_TIMES,
    };
};

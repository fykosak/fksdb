export const ACTION_SUBMIT_SUCCESS = 'ACTION_SUBMIT_SUCCESS';

export function submitSuccess<D>(data: D) {
    return {
        data,
        type: ACTION_SUBMIT_SUCCESS,
    };
}

export const ACTION_SUBMIT_FAIL = 'ACTION_SUBMIT_FAIL';
export const submitFail = (error) => {
    return {
        error,
        type: ACTION_SUBMIT_FAIL,
    };
};

export const ACTION_SUBMIT_START = 'ACTION_SUBMIT_START';
export const submitStart = () => {

    return {
        type: ACTION_SUBMIT_START,
    };
};

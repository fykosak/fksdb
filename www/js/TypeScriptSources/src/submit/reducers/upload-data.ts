import {
    ADD_UPLOAD_SUBMITS,
    NEW_DATA_ARRIVED,
} from '../actions/upload-data';

const addUploadSubmits = (state, action) => {
    return {...action.data};
};

const newDataArrived = (state, action) => {
    return {
        ...state,
        ...action.data,
    };
};
export const uploadData = (state = {}, action) => {
    switch (action.type) {
        case ADD_UPLOAD_SUBMITS:
            return addUploadSubmits(state, action);
        case NEW_DATA_ARRIVED:
            return newDataArrived(state, action);
        default:
            return state;
    }
};

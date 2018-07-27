import { IAccommodationState } from '../reducer/accommodation';

export const ACTION_CHANGE_ACCOMMODATION = 'ACTION_CHANGE_ACCOMMODATION';

export const changeAccommodation = (date: string, accommodationId: number) => {
    return {
        accommodationId,
        date,
        type: ACTION_CHANGE_ACCOMMODATION,
    };
};

export const ACTION_SET_INITIAL_DATA = 'ACTION_SET_INITIAL_DATA';

export const setInitialData = (data: IAccommodationState) => {
    return {
        data,
        type: ACTION_SET_INITIAL_DATA,
    };
};

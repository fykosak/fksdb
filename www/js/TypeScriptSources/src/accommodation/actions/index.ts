export const ACTION_CHANGE_ACCOMMODATION = 'ACTION_CHANGE_ACCOMMODATION';

export const changeAccommodation = (date: string, accommodationId: number) => {
    return {
        accommodationId,
        date,
        type: ACTION_CHANGE_ACCOMMODATION,
    };
};

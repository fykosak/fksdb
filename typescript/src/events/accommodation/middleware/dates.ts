interface RecalculatedDates {
    fromDate: Date;
    toDate: Date;
}

export const recalculateDate = (date: string): RecalculatedDates => {
    const fromDate = new Date(date);
    const toDate = new Date(fromDate.getTime() + (24 * 60 * 60 * 1000));
    return {fromDate, toDate};
};

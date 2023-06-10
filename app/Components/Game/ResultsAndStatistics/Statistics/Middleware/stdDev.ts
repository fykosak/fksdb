export interface StdDevOutput {
    average: number;
    standardDeviation: number;
}

export const getAverageNStandardDeviation = (data: number[]): StdDevOutput => {

    if (data.length < 2) {
        return {average: NaN, standardDeviation: NaN};
    }
    const sum = data.reduce((midSum, value) => {
        return midSum + value;
    }, 0);

    const average = sum / data.length;
    const s = data.reduce((prevValue, value) => {
        const d = average - value;
        return prevValue + (d * d);
    }, 0);

    return {
        average,
        standardDeviation: Math.sqrt(s / (data.length - 1)),
    };
};

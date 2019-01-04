export const getAverageNStandardDeviation = (data: number[]): { average: number; standardDeviation: number } => {

    if (data.length < 2) {
        return {average: NaN, standardDeviation: NaN};
    }
    const sum = data.reduce((midSum, value) => {
        midSum += value;
        return midSum;
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

import { getAverageNStandardDeviation } from './std-dev';
import { ISubmit } from '../../../helpers/interfaces';

export interface IPreprocessedSubmit extends ISubmit {
    timestamp: number;
}

export const calculateCorrelation = (
    firstTeamData: { [taskId: number]: IPreprocessedSubmit },
    secondTeamData: { [taskId: number]: IPreprocessedSubmit },
    threshold: number = 120000,
) => {
    const deltas = [];
    let countTotal = 0;
    let countFiltered = 0;
    for (const taskId in firstTeamData) {
        if (firstTeamData.hasOwnProperty(taskId) && secondTeamData.hasOwnProperty(taskId)) {
            const firstSubmit = firstTeamData[taskId];
            const secondSubmit = secondTeamData[taskId];
            const delta = Math.abs(firstSubmit.timestamp - secondSubmit.timestamp);
            countTotal++;
            if (delta < threshold) {
                countFiltered++;
            }
            deltas.push(delta);

        }
    }
    return {avgNStdDev: getAverageNStandardDeviation(deltas), countTotal, countFiltered};
};

export const getTimeLabel = (average: number, standardDeviation: number): string => {
    if (isNaN(average) || isNaN(standardDeviation)) {
        return 'NaN';
    }
    const h = Math.floor(average / (3600 * 1000));
    average %= (3600 * 1000);
    const m = Math.floor(average / (60 * 1000));
    average %= (60 * 1000);
    const s = Math.floor(average / 10) / 100;
    return (h ? (h + 'h ') : '') +
        (m ? (m + 'm ') : '') +
        s + 's' +
        (' +- ' + (standardDeviation ? (Math.floor(standardDeviation / 10) / 100) : ''));
};

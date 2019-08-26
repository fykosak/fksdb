import { Submit } from '../../../helpers/interfaces';
import {
    getAverageNStandardDeviation,
    StdDevOutput,
} from './stdDev';

export interface PreprocessedSubmit extends Submit {
    timestamp: number;
}

export const calculateCorrelation = (
    firstTeamData: { [taskId: number]: PreprocessedSubmit },
    secondTeamData: { [taskId: number]: PreprocessedSubmit },
    threshold: number = 120000,
): { avgNStdDev: StdDevOutput; countTotal: number; countFiltered: number } => {
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

export const getTimeLabel = (averageTime: number, standardDeviation: number): string => {
    if (isNaN(averageTime) || isNaN(standardDeviation)) {
        return 'NaN';
    }
    const h = Math.floor(averageTime / (3600 * 1000));
    averageTime %= (3600 * 1000);
    const m = Math.floor(averageTime / (60 * 1000));
    averageTime %= (60 * 1000);
    const s = Math.floor(averageTime / 10) / 100;
    return (h ? (h + 'h ') : '') +
        (m ? (m + 'm ') : '') +
        s + 's' +
        (' +- ' + (standardDeviation ? (Math.floor(standardDeviation / 10) / 100) : ''));
};

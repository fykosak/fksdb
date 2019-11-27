import { Submits } from '../../../helpers/interfaces';
import { PreprocessedSubmit } from './correlation';

export interface SubmitsForTeams {
    [teamId: number]: {
        [taskId: number]: PreprocessedSubmit;
    };
}

export const calculateSubmitsForTeams = (submits: Submits): SubmitsForTeams => {
    const submitsForTeams: SubmitsForTeams = {};
    for (const index in submits) {
        if (submits.hasOwnProperty(index)) {
            const submit = submits[index];
            const {teamId, taskId: taskId} = submit;
            submitsForTeams[teamId] = submitsForTeams[teamId] || {};
            submitsForTeams[teamId][taskId] = {
                ...submit,
                timestamp: (new Date(submit.created)).getTime(),
            };
        }
    }
    return submitsForTeams;
};

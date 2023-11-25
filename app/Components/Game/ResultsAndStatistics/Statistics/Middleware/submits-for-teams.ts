import { Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/submit-model';
import { PreprocessedSubmit } from './correlation';

export interface SubmitsForTeams {
    [teamId: number]: {
        [taskId: number]: PreprocessedSubmit;
    };
}

export const calculateSubmitsForTeams = (submits: Submits): SubmitsForTeams => {
    const submitsForTeams: SubmitsForTeams = {};
    for (const index in submits) {
        if (Object.hasOwn(submits,index)) {
            const submit = submits[index];
            const {teamId, taskId: taskId} = submit;
            submitsForTeams[teamId] = submitsForTeams[teamId] || {};
            submitsForTeams[teamId][taskId] = {
                ...submit,
                timestamp: (new Date(submit.modified)).getTime(),
            };
        }
    }
    return submitsForTeams;
};

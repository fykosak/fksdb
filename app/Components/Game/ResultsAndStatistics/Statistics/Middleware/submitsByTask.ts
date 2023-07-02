import { SubmitModel, Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/SubmitModel';

export interface SubmitsByTask {
    [time: number]: {
        [points: number]: number;
    };
}

export const submitsByTask = (
    submits: Submits,
    activeTaskId: number,
    aggregationTime: number,
    activePoints: number = null,
): SubmitsByTask => {
    const taskTimeSubmits: SubmitsByTask = {};
    for (const index in submits) {
        if (Object.hasOwn(submits,index)) {
            const submit: SubmitModel = submits[index];
            if (submit.taskId === activeTaskId) {
                if (submit.points > 0) {
                    if (!activePoints || activePoints === submit.points) {
                        const ms = (new Date(submit.modified)).getTime();
                        const c = Math.floor(ms / aggregationTime);
                        taskTimeSubmits[c] = taskTimeSubmits[c] || {1: 0, 2: 0, 3: 0, 5: 0};
                        taskTimeSubmits[c][submit.points]++;
                    }
                }
            }
        }
    }
    return taskTimeSubmits;
};

import { SubmitModel } from 'FKSDB/Models/ORM/Models/Fyziklani/submit-model';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/task-model';
import * as React from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { setNewState } from '../../actions/stats';
import './progress.scss';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

type StatItem<AvailablePoints extends number> =
    { total: number; }
    & { [points in AvailablePoints]: number; }
    & TaskModel;

interface Stats<AvailablePoints extends number> {
    [taskId: number]: StatItem<AvailablePoints>;
}

interface Props<AvailablePoints extends number> {
    availablePoints: Array<AvailablePoints>;
}

export default function Progress({availablePoints}: Props<5 | 3 | 2 | 1>) {
    const submits = useSelector((state: Store) => state.data.submits);
    const tasks = useSelector((state: Store) => state.data.tasks);
    const tasksSubmits: Stats<5 | 3 | 2 | 1> = {};
    const dispatch = useDispatch();
    for (const task of tasks) {
        const {taskId} = task;
        tasksSubmits[taskId] = {
            ...task,
            5: 0,
            3: 0,
            2: 0,
            1: 0,
            total: 0,
        };
    }
    let max = 0;
    for (const index in submits) {
        if (Object.hasOwn(submits, index)) {
            const submit: SubmitModel = submits[index];
            const {taskId, points} = submit;
            if (Object.hasOwn(tasksSubmits, taskId)) {
                tasksSubmits[taskId][points]++;
                tasksSubmits[taskId].total++;
                if (tasksSubmits[taskId].total > max) {
                    max = tasksSubmits[taskId].total;
                }
            }
        }
    }

    const rows = [];
    for (const index in tasksSubmits) {
        if (Object.hasOwn(tasksSubmits, index)) {
            const submit: StatItem<5 | 3 | 2 | 1> = tasksSubmits[index];

            rows.push(
                <div className="row" key={index}>
                    <div className="col-lg-2">
                        <a
                            href="#"
                            onClick={() => dispatch(setNewState({taskId: +submit.taskId}))}
                        >
                            {submit.label + '-'}
                        </a>
                    </div>
                    <div className="col-lg-10">
                        <div className="progress">
                            {availablePoints.map((value, i) => {
                                return <div
                                    className="progress-bar"
                                    key={i}
                                    style={{
                                        '--bar-color': 'var(--color-fof-points-' + value + ')',
                                        width: (submit[value] / max) * 100 + '%',
                                    } as React.CSSProperties}>
                                    {submit[value]}
                                </div>;
                            })}
                        </div>
                    </div>
                </div>,
            );

        }
    }
    return <div className="chart chart-game-task-progress">{rows}</div>;
}

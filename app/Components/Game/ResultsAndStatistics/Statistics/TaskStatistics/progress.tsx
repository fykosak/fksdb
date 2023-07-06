import { SubmitModel, Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/submit-model';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/task-model';
import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';
import { setNewState } from '../../actions/stats';
import './progress.scss';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface StateProps {
    tasks: TaskModel[];
    submits: Submits;
}

interface DispatchProps {
    onChangeTask(taskId: number): void;
}

interface StatItem extends TaskModel {
    5: number;
    3: number;
    2: number;
    1: number;
    total: number;
}

interface Stats {
    [taskId: number]: StatItem;
}

interface OwnProps {
    availablePoints: number[];
}

function Progress(props: StateProps & DispatchProps & OwnProps) {
    const {submits, tasks, onChangeTask, availablePoints} = props;
    const tasksSubmits: Stats = {};

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
            const submit: StatItem = tasksSubmits[index];

            rows.push(
                <div className="row" key={index}>
                    <div className="col-lg-2">
                        <a href="#" onClick={() => {
                            onChangeTask(submit.taskId);
                        }}>
                            {submit.label + '-'}</a>
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

const mapStateToProps = (state: Store): StateProps => {
    return {
        submits: state.data.submits,
        tasks: state.data.tasks,
    };
};
const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onChangeTask: (taskId) => dispatch(setNewState({taskId: +taskId})),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Progress);

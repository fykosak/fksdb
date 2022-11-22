import { SubmitModel, Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/SubmitModel';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';
import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';
import { setNewState } from '../actions';
import { StatisticStore } from '../Reducers';
import './progress.scss';

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

class Progress extends React.Component<StateProps & DispatchProps & OwnProps> {
    public render() {
        const {submits, tasks, onChangeTask, availablePoints} = this.props;
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
            if (submits.hasOwnProperty(index)) {
                const submit: SubmitModel = submits[index];
                const {taskId, points} = submit;
                if (tasksSubmits.hasOwnProperty(taskId)) {
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
            if (tasksSubmits.hasOwnProperty(index)) {
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
                                        data-points={value}
                                        style={{
                                            width: (submit[value] / max) * 100 + '%',
                                        }}>
                                        {submit[value]}
                                    </div>;
                                })}
                            </div>
                        </div>
                    </div>,
                );

            }
        }
        return (<div className="chart chart-game-task-progress">{rows}</div>);
    }
}

const mapStateToProps = (state: StatisticStore): StateProps => {
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

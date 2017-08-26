import * as React from 'react';
import { connect } from 'react-redux';
import {
    ISubmit,
    ISubmits,
    ITask,
} from '../../../../../helpers/interfaces';
import { getColorByPoints } from '../../../../../helpers/pie/index';
import { IStore } from '../../../../../reducers/index';

interface IState {
    tasks?: ITask[];
    submits?: ISubmits;
}

class TaskStats extends React.Component<IState, {}> {
    public render() {
        const {submits, tasks} = this.props;
        const tasksSubmits = {};

        for (const task of tasks) {
            const {task_id} = task;
            tasksSubmits[task_id] = {
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
                const submit: ISubmit = submits[index];
                const {task_id, points} = submit;
                if (tasksSubmits.hasOwnProperty(task_id)) {
                    tasksSubmits[task_id][points]++;
                    tasksSubmits[task_id].total++;
                    if (tasksSubmits[task_id].total > max) {
                        max = tasksSubmits[task_id].total;
                    }
                }
            }
        }

        const rows = Object.keys(tasksSubmits).map((index) => {
            const submit = tasksSubmits[index];

            return (
                <div className="row">
                    <div className="col-lg-1">{submit.label + '-'}</div>
                    <div className="col-lg-11">
                        <div className="progress">
                            <div className="progress-bar"
                                 data-points="5"
                                 style={{
                                     'background-color': getColorByPoints(5),
                                     'width': (submit[5] / max) * 100 + '%',
                                 }}>{submit[5]}</div>
                            <div className="progress-bar"
                                 data-points="3"
                                 style={{
                                     'background-color': getColorByPoints(3),
                                     'width': (submit[3] / max) * 100 + '%',
                                 }}>{submit[3]}</div>
                            <div className="progress-bar"
                                 data-points="2"
                                 style={{
                                     'background-color': getColorByPoints(2),
                                     'width': (submit[2] / max) * 100 + '%',
                                 }}>{submit[2]}</div>
                            <div className="progress-bar"
                                 data-points="1"
                                 style={{
                                     'background-color': getColorByPoints(1),
                                     'width': (submit[1] / max) * 100 + '%',
                                 }}>{submit[1]}</div>
                        </div>
                    </div>
                </div>
            );

        });
        return (<div>{rows}</div>);
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        submits: state.results.submits,
        tasks: state.results.tasks,
    };
};

export default connect(mapStateToProps, null)(TaskStats);

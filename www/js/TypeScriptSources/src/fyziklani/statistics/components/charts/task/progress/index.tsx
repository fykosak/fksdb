import * as React from 'react';
import { connect } from 'react-redux';
import {
    ISubmit,
    ISubmits,
    ITask,
} from '../../../../../helpers/interfaces/';
import { getColorByPoints } from '../../../../middleware/charts/colors';
import { IFyziklaniStatisticsStore } from '../../../../reducers';

interface IState {
    tasks?: ITask[];
    submits?: ISubmits;
}

class TaskStats extends React.Component<IState, {}> {
    public render() {
        const {submits, tasks} = this.props;
        const tasksSubmits = {};

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
                const submit: ISubmit = submits[index];
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

        const rows = Object.keys(tasksSubmits).map((index) => {
            const submit = tasksSubmits[index];

            return (
                <div className="row" key={index}>
                    <div className="col-lg-1">{submit.label + '-'}</div>
                    <div className="col-lg-11">
                        <div className="progress">
                            <div className="progress-bar"
                                 data-points="5"
                                 style={{
                                     backgroundColor: getColorByPoints(5),
                                     width: (submit[5] / max) * 100 + '%',
                                 }}>{submit[5]}</div>
                            <div className="progress-bar"
                                 data-points="3"
                                 style={{
                                     backgroundColor: getColorByPoints(3),
                                     width: (submit[3] / max) * 100 + '%',
                                 }}>{submit[3]}</div>
                            <div className="progress-bar"
                                 data-points="2"
                                 style={{
                                     backgroundColor: getColorByPoints(2),
                                     width: (submit[2] / max) * 100 + '%',
                                 }}>{submit[2]}</div>
                            <div className="progress-bar"
                                 data-points="1"
                                 style={{
                                     backgroundColor: getColorByPoints(1),
                                     width: (submit[1] / max) * 100 + '%',
                                 }}>{submit[1]}</div>
                        </div>
                    </div>
                </div>
            );

        });
        return (<div>{rows}</div>);
    }
}

const mapStateToProps = (state: IFyziklaniStatisticsStore): IState => {
    return {
        submits: state.data.submits,
        tasks: state.data.tasks,
    };
};

export default connect(mapStateToProps, null)(TaskStats);

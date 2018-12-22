import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { lang } from '../../../../../i18n/i18n';
import { ITask } from '../../../../helpers/interfaces';
import { setTaskId } from '../../../actions';
import { IFyziklaniStatisticsStore } from '../../../reducers';
import Progress from './progress/';
import Timeline from './timeline/';

interface IState {
    tasks?: ITask[];
    taskId?: number;

    onChangeTask?(id: number): void;
}

class TaskStats extends React.Component<IState, {}> {
    public render() {
        const {onChangeTask, taskId, tasks} = this.props;
        const taskSelect = (
            <p>
                <select className="form-control" onChange={(event) => {
                    onChangeTask(+event.target.value);
                }}>
                    <option value={null}>--select team--</option>
                    {tasks.map((task) => {
                        return (<option key={task.taskId} value={task.taskId}>{task.label}</option>);
                    })}
                </select>
            </p>
        );
        return (
            <div>
                <h2>{lang.getText('Global statistics')}</h2>
                <div className={'fyziklani-chart-container'}>
                    <h3>{lang.getText('Počet vyriešených úloh')}</h3>
                    <Progress/>
                </div>

                <h2>{lang.getText('Statistics from single problem')}</h2>
                {taskSelect}
                {taskId && <Timeline/>}
            </div>
        );
    }
}

const mapStateToProps = (state: IFyziklaniStatisticsStore): IState => {
    return {
        taskId: state.statistics.taskId,
        tasks: state.data.tasks,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): IState => {
    return {
        onChangeTask: (teamId) => dispatch(setTaskId(+teamId)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(TaskStats);

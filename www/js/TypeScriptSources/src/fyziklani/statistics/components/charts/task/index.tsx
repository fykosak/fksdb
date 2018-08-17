import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { lang } from '../../../../../i18n/i18n';
import { ITask } from '../../../../helpers/interfaces';
import { setTaskId } from '../../../actions';
import { IFyziklaniStatisticsStore } from '../../../reducers';
import Progress from './progress/';
import Timeline from './timeline/';

interface IState {
    tasks?: ITask[];
    onChangeTask?: (id: number) => void;
    taskId?: number;
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
                <h2>{lang.getText('Globálne štatistiky')}</h2>
                <Progress/>

                <h2>{lang.getText('Štatistiky jednotlivých úloh')}</h2>
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

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniStatisticsStore>): IState => {
    return {
        onChangeTask: (teamId) => dispatch(setTaskId(+teamId)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(TaskStats);

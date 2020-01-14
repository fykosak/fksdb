import { lang } from '@i18n/i18n';
import TimeDisplay from '@shared/components/displays/time';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { Task } from '../../../../../helpers/interfaces';
import {
    setAggregationTime,
    setFromDate,
    setTaskId,
    setToDate,
} from '../../../../actions';
import { Store as StatisticsStore } from '../../../../reducers';

interface StateProps {
    aggregationTime: number;
    tasks: Task[];
    taskId: number;
    fromDate: Date;
    toDate: Date;
    gameStart: Date;
    gameEnd: Date;
}

interface DispatchProps {
    onChangeAggregationTime(time: number): void;

    onChangeTask(id: number): void;

    onSetFromDate(date: Date): void;

    onSetToDate(date: Date): void;
}

class Options extends React.Component<StateProps & DispatchProps, {}> {

    public componentDidMount() {
        const {onSetFromDate, onSetToDate, gameEnd, gameStart} = this.props;
        onSetFromDate(gameStart);
        onSetToDate(gameEnd);
    }

    public render() {
        const {
            aggregationTime,
            onSetFromDate,
            onSetToDate,
            onChangeAggregationTime,
            onChangeTask,
            tasks,
            taskId,
            gameStart,
            gameEnd,
            fromDate,
            toDate,
        } = this.props;

        if (!toDate || !fromDate) {
            return null;
        }
        return (
            <>
                <h3>{lang.getText('Options')}</h3>

                <div className={'row'}>
                    <div className={'col-6'}>
                        <div className={'form-group'}>
                            <label>Task</label>
                            <select value={taskId} className="form-control" onChange={(event) => {
                                onChangeTask(+event.target.value);
                            }}>
                                <option value={null}>--{lang.getText('select task')}--</option>
                                {tasks.map((task) => {
                                    return (<option key={task.taskId} value={task.taskId}>{task.label}</option>);
                                })}
                            </select>
                        </div>
                    </div>
                    <div className={'col-6'}>
                        <div className={'form-group'}>
                            <label>{lang.getText('Aggregation time')}</label>
                            <input type={'range'} max={30 * 60 * 1000} min={60 * 1000}
                                   value={aggregationTime}
                                   step={60 * 1000}
                                   className={'form-control'}
                                   onChange={(e) => {
                                       onChangeAggregationTime(+e.target.value);
                                   }}/>
                            <span className={'form-text'}>{aggregationTime / (60 * 1000)} min</span>
                        </div>
                    </div>
                </div>
                <div className={'row'}>
                    <div className={'col-6'}>
                        <div className={'form-group'}>
                            <label>{lang.getText('From')}</label>
                            <input type={'range'}
                                   className={'form-control'}
                                   value={fromDate.getTime()}
                                   min={gameStart.getTime()}
                                   max={toDate.getTime()}
                                   step={60 * 1000}
                                   onChange={(e) => {
                                       onSetFromDate(new Date(+e.target.value));
                                   }}/>
                            <span className={'form-text'}><TimeDisplay date={fromDate.toISOString()}/></span>
                        </div>
                    </div>
                    <div className={'col-6'}>
                        <div className={'form-group'}>
                            <label>{lang.getText('To')}</label>
                            <input type={'range'}
                                   className={'form-control'}
                                   value={toDate.getTime()}
                                   min={fromDate.getTime()}
                                   max={gameEnd.getTime()}
                                   step={60 * 1000}
                                   onChange={(e) => {
                                       onSetToDate(new Date(+e.target.value));
                                   }}/>
                            <span className={'form-text'}><TimeDisplay date={toDate.toISOString()}/></span>
                        </div>
                    </div>
                </div>
            </>
        );
    }
}

const mapStateToProps = (state: StatisticsStore): StateProps => {
    return {
        aggregationTime: state.statistics.aggregationTime,
        fromDate: state.statistics.fromDate,
        gameEnd: new Date(state.timer.gameEnd),
        gameStart: new Date(state.timer.gameStart),
        taskId: state.statistics.taskId,
        tasks: state.data.tasks,
        toDate: state.statistics.toDate,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onChangeAggregationTime: (time: number) => dispatch(setAggregationTime(time)),
        onChangeTask: (teamId) => dispatch(setTaskId(+teamId)),
        onSetFromDate: (date: Date) => dispatch(setFromDate(date)),
        onSetToDate: (date: Date) => dispatch(setToDate(date)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Options);

import * as React from 'react';
import { useContext } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { setNewState } from '../../actions/stats';
import { TranslatorContext } from '@translator/context';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

export default function Options() {
    const translator = useContext(TranslatorContext);

    const aggregationTime = useSelector((state: Store) => state.statistics.aggregationTime);
    const taskId = useSelector((state: Store) => state.statistics.taskId);
    const tasks = useSelector((state: Store) => state.data.tasks);
    const dispatch = useDispatch()

    return <>
        <h3>{translator.getText('Options')}</h3>
        <div className="row">
            <div className="col-6">
                <div className="form-group">
                    <label>Task</label>
                    <select
                        value={taskId}
                        className="form-control"
                        onChange={(event) => dispatch(setNewState({taskId: +event.target.value}))}
                    >
                        <option value={null}>--{translator.getText('select task')}--</option>
                        {tasks.map((task) => {
                            return <option key={task.taskId} value={task.taskId}>{task.label}</option>;
                        })}
                    </select>
                </div>
            </div>
            <div className="col-6">
                <div className="form-group">
                    <label>{translator.getText('Aggregation time')}</label>
                    <input
                        type="range"
                        max={30}
                        min={1}
                        value={aggregationTime / 60000}
                        step={1}
                        className="form-range"
                        onChange={(e) => dispatch(setNewState({aggregationTime: +e.target.value * 60000}))}
                    />
                    <span className="form-text">{aggregationTime / (60 * 1000)} min</span>
                </div>
            </div>
        </div>
    </>;
}

import * as React from 'react';
import {
    ITask,
    ITeam,
} from '../../../helpers/interfaces';

interface IProps {
    submits: any;
    team: ITeam;
    tasks: Array<ITask>;
    visible: boolean;
}

export default class TeamRow extends React.Component<IProps, void> {

    public render() {
        const {submits, team, tasks, visible} = this.props;

        let count = 0;
        let sum = 0;
        const cools = tasks.map((task, taskIndex) => {
            // find submit
            const {task_id} = task;

            const submit = submits[task_id] || null;
            const points = submit ? submit.points : null;

            if (points !== null) {
                count++;
                sum += +points;
            }
            return (<td data-points={points} key={taskIndex}>{ points }</td>);
        });

        const average = count > 0 ? Math.round(sum / count * 100) / 100 : '-';
        return (
            <tr style={{display: visible ? '' : 'none'}}>
                <td>{team.name}</td>
                <td className="sum">{sum}</td>
                <td>{count}</td>
                <td>{average}</td>
                {cools}
            </tr>
        );
    };
}

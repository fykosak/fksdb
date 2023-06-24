import { Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/SubmitModel';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';

interface OwnProps {
    submits: Submits;
    team: TeamModel;
    tasks: TaskModel[];
    visible: boolean;
}

export default class Row extends React.Component<OwnProps, never> {

    public render() {
        const {submits, team, tasks, visible} = this.props;

        let count = 0;
        let sum = 0;
        const cools = tasks.map((task, taskIndex) => {
            // find submit
            const {taskId} = task;
            const submit = submits[taskId] || null;
            const points = submit ? submit.points : null;

            if (points !== null && points !== 0) {
                count++;
                sum += +points;
            }
            return (<td data-points={points} key={taskIndex}>{points ? points : null}</td>);
        });

        const average = count > 0 ? Math.round(sum / count * 100) / 100 : '-';
        return (
            <tr style={{display: visible ? '' : 'none'}}>
                <td/>
                <td>{team.name}</td>
                <td className="sum">{sum}</td>
                <td>{count}</td>
                <td>{average}</td>
                {cools}
            </tr>
        );
    }
}

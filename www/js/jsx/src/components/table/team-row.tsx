import * as React from 'react';


interface IProps {
    submits: any;
    team: any;
    tasks: Array<any>;
}

export default class TeamRow extends React.Component<IProps, void> {

    public render() {
        let {submits, team, tasks} = this.props;
        let cools = [];

        let count = 0;
        let sum = 0;
        tasks.forEach((task, taskIndex)=> {
            // find submit
            let {task_id}= task;

            let submit = submits[task_id] || null;
            let points = submit ? submit.points : null;

            if (points !== null) {
                count++;
                sum += +points;
            }
            cools.push(<td data-points={points} key={taskIndex}>{ points }</td>);
        });

        let average = count > 0 ? Math.round(sum / count * 100) / 100 : '-';
        return (
            <tr>
                <td>{team.name}</td>
                <td className="sum">{sum}</td>
                <td>{count}</td>
                <td>{average}</td>
                {cools}
            </tr>
        );
    };
}

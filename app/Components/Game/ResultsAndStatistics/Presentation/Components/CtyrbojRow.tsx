import { Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/SubmitModel';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';

interface OwnProps {
    submits: Submits;
    team: TeamModel;
    tasks: TaskModel[];
}

export default class Row extends React.Component<OwnProps, never> {

    public render() {
        const {submits, team, tasks} = this.props;
        let sum = 0;
        const taskMap = {
            A: [],
            B: [],
            C: [],
            D: [],
        }
        tasks.map((task, taskIndex) => {
            // find submit
            const group = task.label.substring(0, 1);
            const {taskId} = task;
            const submit = submits[taskId] || null;
            const points = submit ? submit.points : null;

            if (points !== null || points !== 0) {
                sum += +points;
            }
            return taskMap[group].push(<span
                className="text-center"
                data-points={points}
                key={taskIndex}
            />);
        });
        return <tr>
            <td><strong>{team.name}</strong></td>
            <td>{sum}</td>
            <td data-ctyrboj-label="A">
                    <span
                        className="d-flex justify-content-evenly align-item-center">{taskMap.A}</span></td>
            <td data-ctyrboj-label="B">
                    <span
                        className="d-flex justify-content-evenly align-item-center">{taskMap.B}</span></td>
            <td data-ctyrboj-label="C">
                    <span
                        className="d-flex justify-content-evenly align-item-center">{taskMap.C}</span></td>
            <td data-ctyrboj-label="D">
                    <span
                        className="d-flex justify-content-evenly align-item-center">{taskMap.D}</span></td>
        </tr>;
    }
}

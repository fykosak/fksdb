import * as React from 'react';
import {
    ITask,
    ITeam,
} from '../../helpers/interfaces/';

interface IProps {
    code: string;
    tasks: ITask[];
    teams: ITeam[];
}

export default class TaskInput extends React.Component<IProps, {}> {

    public render() {
        const {code: value, tasks, teams} = this.props;

        if (!value) {
            return null;
        }
        const length = value.length;
        const code = '0'.repeat(9 - length) + value;

        const matchedTeam = code.match(/^([0-9]+)/);

        const team = teams.filter((currentTeam) => {
            return currentTeam.teamId === +matchedTeam[1];
        })[0];

        const matchedLabel = code.match(/([a-zA-Z]{2})/);
        let task = null;
        if (matchedLabel) {
            // const label = extractTaskLabel(code);
            task = tasks.filter((currentTask) => {
                return currentTask.label === matchedLabel[1].toUpperCase();
            })[0];
        }

        return (
            <ul>
                <li>Team: {team ? (<span className="text-success">{team.name}</span>) : (
                    <span className="text-danger">Invalid team</span>)}</li>
                <li>Task: {task ? (<span className="text-success">{task.name}</span>) : (
                    <span className="text-danger">Invalid task</span>)}</li>
            </ul>
        );
    }
}

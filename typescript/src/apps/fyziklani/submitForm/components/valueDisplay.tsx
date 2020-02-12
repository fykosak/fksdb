import { getFullCode, getTask, getTeam } from '@apps/fyziklani/submitForm/middleware/form';
import * as React from 'react';
import {
    Task,
    Team,
} from '../../helpers/interfaces/';

interface OwnProps {
    code: string;
    tasks: Task[];
    teams: Team[];
}

export default class ValueDisplay extends React.Component<OwnProps, {}> {

    public render() {
        const {code: value, tasks, teams} = this.props;

        if (!value) {
            return null;
        }
        const fullCode = getFullCode(value);
        const team = getTeam(fullCode, teams);
        const task = getTask(fullCode, tasks);

        return (
            <>
                <h3 className={'fyziklani-headline-color'}>Team</h3>
                {team ? (<span className="text-success">{team.name}</span>) : (
                    <span className="text-danger">Invalid team</span>)}
                <h3 className={'fyziklani-headline-color'}>Task</h3>
                {task ? (<span className="text-success">{task.name}</span>) : (
                    <span className="text-danger">Invalid task</span>)}
            </>
        );
    }
}

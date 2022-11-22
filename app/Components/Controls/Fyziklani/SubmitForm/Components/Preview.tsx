import { translator } from '@translator/translator';
import {
    getFullCode,
    getTask,
    getTeam,
} from 'FKSDB/Components/Controls/Fyziklani/SubmitForm/middleware';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';

interface OwnProps {
    code: string;
    tasks: TaskModel[];
    teams: TeamModel[];
}

export default class Preview extends React.Component<OwnProps> {

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
                <h3>{translator.getText('Team')}</h3>
                {team ? (<span className="text-success">{team.name}</span>) : (
                    <span className="text-danger">{translator.getText('Invalid team')}</span>)}
                <h3>{translator.getText('Task')}</h3>
                {task ? (<span className="text-success">{task.name}</span>) : (
                    <span className="text-danger">{translator.getText('Invalid task')}</span>)}
            </>
        );
    }
}

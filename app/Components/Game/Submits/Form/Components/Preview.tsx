import { translator } from '@translator/translator';
import { getTask, getTeam } from '../middleware';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';

interface OwnProps {
    code: string;
    tasks: TaskModel[];
    teams: TeamModel[];
}

export default class Preview extends React.Component<OwnProps, never> {

    public render() {
        const {code: value, tasks, teams} = this.props;
        if (!value) {
            return null;
        }
        let error = null;
        let task = null;
        let team = null;
        try {
            team = getTeam(value, teams);
            task = getTask(value, tasks);
        } catch (e) {
            error = e.message;
        }
        return (
            <>
                {error && <span className="text-danger">{error}</span>}
                {team && <>
                    <h3>{translator.getText('Team')}</h3>
                    <span className="text-success">{team.name}</span>
                </>}
                {task && <>
                    <h3>{translator.getText('Task')}</h3>
                    <span className="text-success">{task.name}</span>
                </>}
            </>
        );
    }
}

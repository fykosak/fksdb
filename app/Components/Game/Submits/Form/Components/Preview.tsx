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
        try {
            const team = getTeam(value, teams);
            const task = getTask(value, tasks);
            return <>
                {team && <>
                    <h3>{translator.getText('Team')}</h3>
                    <span className="text-success">{team.name}</span>
                </>}
                {task && <>
                    <h3>{translator.getText('Task')}</h3>
                    <span className="text-success">{task.name}</span>
                </>}
            </>;
        } catch (e) {
            return <span className="text-danger">{e.message}</span>
        }

    }
}

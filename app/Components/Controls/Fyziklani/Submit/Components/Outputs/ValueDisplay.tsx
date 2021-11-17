import { translator } from '@translator/translator';
import {
    getFullCode,
    getTask,
    getTeam,
} from 'FKSDB/Components/Controls/Fyziklani/Submit/middleware';
import { ModelFyziklaniTask } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTask';
import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';
import * as React from 'react';

interface OwnProps {
    code: string;
    tasks: ModelFyziklaniTask[];
    teams: ModelFyziklaniTeam[];
}

export default class ValueDisplay extends React.Component<OwnProps, Record<string, never>> {

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
                <h3 className={'fyziklani-headline-color'}>{translator.getText('Team')}</h3>
                {team ? (<span className="text-success">{team.name}</span>) : (
                    <span className="text-danger">{translator.getText('Invalid team')}</span>)}
                <h3 className={'fyziklani-headline-color'}>{translator.getText('Task')}</h3>
                {task ? (<span className="text-success">{task.name}</span>) : (
                    <span className="text-danger">{translator.getText('Invalid task')}</span>)}
            </>
        );
    }
}

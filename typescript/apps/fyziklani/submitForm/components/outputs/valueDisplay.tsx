import {
    getFullCode,
    getTask,
    getTeam,
} from '@apps/fyziklani/submitForm/middleware';
import * as React from 'react';
import { ModelFyziklaniTeam } from '@FKSDB/Model/ORM/Models/Fyziklani/ModelFyziklaniTeam';
import { ModelFyziklaniTask } from '@FKSDB/Model/ORM/Models/Fyziklani/ModelFyziklaniTask';
import { translator } from '@translator/Translator';

interface OwnProps {
    code: string;
    tasks: ModelFyziklaniTask[];
    teams: ModelFyziklaniTeam[];
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

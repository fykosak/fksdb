import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/task-model';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/team-model';
import * as React from 'react';
import { useContext } from 'react';
import { TranslatorContext } from '@translator/context';
import TaskCodePreprocessor from 'FKSDB/Components/Game/Submits/task-code-preprocessor';

interface OwnProps {
    code: string;
    tasks: TaskModel[];
    teams: TeamModel[];
}

export default function Preview({code: value, tasks, teams}: OwnProps) {
    const translator = useContext(TranslatorContext);
    if (!value) {
        return null;
    }
    try {
        const preprocessor = new TaskCodePreprocessor(teams, tasks);
        const team = preprocessor.getTeam(value);
        const task = preprocessor.getTask(value);
        preprocessor.getSum(value);
        return <div className="container">
            <div className="row row-cols-1 row-cols-sm-2">
                {team && <div className="col">
                    <h3>{translator.getText('Team')}</h3>
                    <span className="text-success">{team.name}</span>
                </div>}
                {task && <div className="col">
                    <h3>{translator.getText('Task')}</h3>
                    <span className="text-success">{task.name}</span>
                </div>}
            </div>
        </div>;
    } catch (e) {
        return <span className="text-danger">{e.message}</span>
    }
}

import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';
import { TranslatorContext } from '@translator/LangContext';
import TaskCodePreprocessor from 'FKSDB/Components/Game/Submits/TaskCodePreprocessor';

interface OwnProps {
    code: string;
    tasks: TaskModel[];
    teams: TeamModel[];
}

export default class Preview extends React.Component<OwnProps, never> {
    static contextType = TranslatorContext;

    public render() {
        const translator = this.context;
        const {code: value, tasks, teams} = this.props;
        if (!value) {
            return null;
        }
        try {
            const preprocessor = new TaskCodePreprocessor(teams, tasks);
            const team = preprocessor.getTeam(value);
            const task = preprocessor.getTask(value);
            return <div className='container'>
                <div className='row row-cols-1 row-cols-sm-2'>
                    {team && <div className='col'>
                        <h3>{translator.getText('Team')}</h3>
                        <span className="text-success">{team.name}</span>
                    </div>}
                    {task && <div className='col'>
                        <h3>{translator.getText('Task')}</h3>
                        <span className="text-success">{task.name}</span>
                    </div>}
                </div>
            </div>;
        } catch (e) {
            return <span className="text-danger">{e.message}</span>
        }
    }
}

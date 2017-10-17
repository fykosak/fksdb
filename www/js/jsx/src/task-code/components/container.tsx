import * as React from 'react';

import {
    ITask,
    ITeam,
} from '../middleware/interfaces';

import InputsContainer from './inputs-container';
import SubmitButtons from './submit-buttons';

interface IProps {
    tasks: ITask[];
    teams: ITeam[];
}

export default class TaskCode extends React.Component<IProps, {}> {
    public render() {
        const { tasks, teams } = this.props;
        // const store = createStore(app);
        return (
            <div>
                <InputsContainer tasks={tasks} teams={teams}/>
                <SubmitButtons/>
            </div>
        );
    }
}

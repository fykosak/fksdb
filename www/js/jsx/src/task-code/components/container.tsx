import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';

import {
    ITask,
    ITeam,
} from '../middleware/interfaces';

import InputsContainer from './inputs-container';

import { submitStart } from '../actions/index';
import { IStore } from '../reducers/index';

interface IProps {
    tasks: ITask[];
    teams: ITeam[];
}
interface IState {
    onSubmit?: (values: any) => Promise<any>;
}

class TaskCode extends React.Component<IProps & IState, {}> {
    public render() {
        const { tasks, teams, onSubmit } = this.props;
        // const store = createStore(app);
        return (
            <div className="row">
                <div className="col-lg-12 col-md-12">
                    <InputsContainer tasks={tasks} teams={teams} onSubmit={onSubmit}/>
                </div>
            </div>
        );
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onSubmit: (values) => submitStart(dispatch, values),
    };
};

export default connect(null, mapDispatchToProps)(TaskCode);

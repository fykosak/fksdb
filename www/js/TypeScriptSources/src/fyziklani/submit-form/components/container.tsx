import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import Powered from '../../../shared/powered';
import {
    ITask,
    ITeam,
} from '../../helpers/interfaces/';
import { submitStart } from '../actions/';
import {
    IFyziklaniSubmitStore,

} from '../reducers/';
import FormContainer from './form-container';

interface IProps {
    tasks: ITask[];
    teams: ITeam[];
}

interface IState {
    onSubmit?: (values: any) => Promise<any>;
}

class TaskCode extends React.Component<IProps & IState, {}> {
    public render() {
        const {tasks, teams, onSubmit} = this.props;
        return (
            <div className="row">
                <div className="col-lg-12 col-md-12">
                    <FormContainer tasks={tasks} teams={teams} onSubmit={onSubmit}/>
                </div>
                <Powered/>
            </div>
        );
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniSubmitStore>): IState => {
    return {
        onSubmit: (values) => submitStart(dispatch, values),
    };
};

export default connect((): IState => {
    return {};
}, mapDispatchToProps)(TaskCode);

import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { INetteActions } from '../../../app-collector';
import { IResponse } from '../../../fetch-api/middleware/interfaces';
import Powered from '../../../shared/powered';
import {
    ITask,
    ITeam,
} from '../../helpers/interfaces/';
import {
    ISubmitFormRequest,
    submitStart,
} from '../actions/';
import FormContainer from './form-container';

interface IProps {
    tasks: ITask[];
    teams: ITeam[];
    actions: INetteActions;
    availablePoints: number[];
}

interface IState {
    onSubmit?(values: ISubmitFormRequest): Promise<IResponse<void>>;
}

class TaskCode extends React.Component<IProps & IState, {}> {
    public render() {
        const {tasks, teams, onSubmit, availablePoints} = this.props;
        return (
            <div className="row">
                <div className="col-lg-12 col-md-12">
                    <FormContainer tasks={tasks} teams={teams} onSubmit={onSubmit} availablePoints={availablePoints}/>
                </div>
                <Powered/>
            </div>
        );
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action>, ownProps: IProps): IState => {
    return {
        onSubmit: (values: ISubmitFormRequest) => submitStart(dispatch, values, ownProps.actions.save),
    };
};

export default connect((): IState => {
    return {};
}, mapDispatchToProps)(TaskCode);

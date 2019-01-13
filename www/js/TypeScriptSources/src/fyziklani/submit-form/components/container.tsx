import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { NetteActions } from '../../../app-collector';
import { Response } from '../../../fetch-api/middleware/interfaces';
import Powered from '../../../shared/powered';
import {
    Task,
    Team,
} from '../../helpers/interfaces/';
import {
    SubmitFormRequest,
    submitStart,
} from '../actions/';
import FormContainer from './form-container';

interface Props {
    tasks: Task[];
    teams: Team[];
    actions: NetteActions;
    availablePoints: number[];
}

interface State {
    onSubmit?(values: SubmitFormRequest): Promise<Response<void>>;
}

class TaskCode extends React.Component<Props & State, {}> {
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

const mapDispatchToProps = (dispatch: Dispatch<Action>, ownProps: Props): State => {
    return {
        onSubmit: (values: SubmitFormRequest) => submitStart(dispatch, values, ownProps.actions.save),
    };
};

export default connect((): State => {
    return {};
}, mapDispatchToProps)(TaskCode);

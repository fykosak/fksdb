import { NetteActions } from '@appsCollector';
import { Response } from '@fetchApi/middleware/interfaces';
import Powered from '@shared/powered';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import {
    Task,
    Team,
} from '../../helpers/interfaces/';
import {
    SubmitFormRequest,
    submitStart,
} from '../actions/';
import FormContainer from './formContainer';

interface OwnProps {
    tasks: Task[];
    teams: Team[];
    actions: NetteActions;
    availablePoints: number[];
}

interface DispatchProps {
    onSubmit(values: SubmitFormRequest): Promise<Response<void>>;
}

class TaskCode extends React.Component<OwnProps & DispatchProps, {}> {
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

const mapDispatchToProps = (dispatch: Dispatch<Action>, ownProps: OwnProps): DispatchProps => {
    return {
        onSubmit: (values: SubmitFormRequest) => submitStart(dispatch, values, ownProps.actions.getAction('save')),
    };
};

export default connect((): {} => {
    return {};
}, mapDispatchToProps)(TaskCode);

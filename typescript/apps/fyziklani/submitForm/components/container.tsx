import { NetteActions } from '@appsCollector/netteActions';
import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';
import {
    Form,
    InjectedFormProps,
    reduxForm,
} from 'redux-form';
import {
    Task,
    Team,
} from '../../helpers/interfaces';
import {
    SubmitFormRequest, submitStart,
} from '../actions';
import { validate } from '../middleware';
import FormSection from './formSection';

export interface OwnProps {
    tasks: Task[];
    teams: Team[];
    actions: NetteActions;
    availablePoints: number[];
}

interface DispatchProps {
    onSubmit(values: SubmitFormRequest): Promise<any>;
}

class Container extends React.Component<OwnProps & DispatchProps & InjectedFormProps<{ code: string }, OwnProps>> {

    public render() {
        const {valid, submitting, handleSubmit, onSubmit, tasks, teams, availablePoints} = this.props;

        return (
            <Form onSubmit={handleSubmit(onSubmit)}>
                <FormSection
                    availablePoints={availablePoints}
                    tasks={tasks}
                    teams={teams}
                    onSubmit={onSubmit}
                    valid={valid}
                    submitting={submitting}
                    handleSubmit={handleSubmit}
                />
            </Form>
        );
    }
}

export const FORM_NAME = 'codeForm';

const mapDispatchToProps = (dispatch: Dispatch<Action>, ownProps: OwnProps): DispatchProps => {
    return {
        onSubmit: (values: SubmitFormRequest) => submitStart(dispatch, values, ownProps.actions.getAction('save')),
    };
};

export default connect(null, mapDispatchToProps)(
    reduxForm<{ code: string }, any, string>({
        form: FORM_NAME,
        validate,
    })(Container),
);

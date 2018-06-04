import * as React from 'react';
import {
    Form,
    InjectedFormProps,
    reduxForm,
} from 'redux-form';
import {
    ITask,
    ITeam,
} from '../../shared/interfaces';
import { validate } from '../middleware/form';

import FormSection from './form-section';
import { connect } from 'react-redux';

export interface IProps {
    tasks: ITask[];
    teams: ITeam[];
    onSubmit: (values: any) => Promise<any>;
}

class FormContainer extends React.Component<InjectedFormProps & IProps, {}> {

    public render() {
        const {valid, submitting, handleSubmit, onSubmit, tasks, teams} = this.props;

        return (
            <Form onSubmit={handleSubmit(onSubmit)}>
                <FormSection
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

export default reduxForm({
    form: FORM_NAME,
    validate,
})(connect((): {} => {
    return {};
}, (): {} => {
    return {};
})(FormContainer));

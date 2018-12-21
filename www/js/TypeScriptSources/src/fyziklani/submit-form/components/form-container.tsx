import * as React from 'react';
import {
    Form,
    InjectedFormProps,
    reduxForm,
} from 'redux-form';
import {
    ITask,
    ITeam,
} from '../../helpers/interfaces/';
import { ACCESS_KEY } from '../actions';
import { validate } from '../middleware/form';
import FormSection from './form-section';

export interface IProps {
    tasks: ITask[];
    teams: ITeam[];

    onSubmit(values: any): Promise<any>;
}

// { code: string }
class FormContainer extends React.Component<IProps & InjectedFormProps<{ code: string }, IProps>> {

    public render() {
        const {valid, submitting, handleSubmit, onSubmit, tasks, teams} = this.props;

        return (
            <Form onSubmit={handleSubmit(onSubmit)}>
                <FormSection
                    accessKey={ACCESS_KEY}
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

export default reduxForm<{ code: string }, any>({
    form: FORM_NAME,
    validate,
})(FormContainer);

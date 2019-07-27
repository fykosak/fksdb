import { Response } from '@fetchApi/middleware/interfaces';
import * as React from 'react';
import {
    Form,
    InjectedFormProps,
    reduxForm,
} from 'redux-form';
import {
    Task,
    Team,
} from '../../helpers/interfaces/';
import {
    ACCESS_KEY,
    SubmitFormRequest,
} from '../actions';
import { validate } from '../middleware/form';
import FormSection from './FormSection';

export interface Props {
    tasks: Task[];
    teams: Team[];
    availablePoints: number[];

    onSubmit?(values: SubmitFormRequest): Promise<Response<void>>;
}

// { code: string }
class FormContainer extends React.Component<Props & InjectedFormProps<{ code: string }, Props>> {

    public render() {
        const {valid, submitting, handleSubmit, onSubmit, tasks, teams, availablePoints} = this.props;

        return (
            <Form onSubmit={handleSubmit(onSubmit)}>
                <FormSection
                    availablePoints={availablePoints}
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

export default reduxForm<{ code: string }, any, string>({
    form: FORM_NAME,
    validate,
})(FormContainer);

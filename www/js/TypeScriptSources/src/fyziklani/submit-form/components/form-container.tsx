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
    availablePoints: number[];

    onSubmit(values: any): Promise<any>;
}

class FormContainer extends React.Component<IProps & InjectedFormProps<{ code: string }, IProps>, {}> {

    public render() {
        const {valid, submitting, handleSubmit, onSubmit, tasks, teams, availablePoints} = this.props;

        return (
            <Form onSubmit={handleSubmit(onSubmit)}>
                <FormSection
                    accessKey={ACCESS_KEY}
                    tasks={tasks}
                    teams={teams}
                    availablePoints={availablePoints}
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
})(FormContainer);

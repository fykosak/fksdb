import { OwnProps } from './Components/MainForm';
import { FormErrors } from 'redux-form';
import TaskCodePreprocessor from 'FKSDB/Components/Game/Submits/TaskCodePreprocessor';

export const validate = (values: { code?: string }, props: OwnProps): FormErrors<{ code?: string }> => {
    const errors: FormErrors<{ code?: string }> = {};

    if (!values.code) {
        errors.code = 'Code is empty.';
        return errors;
    }
    try {
        const preprocessor = new TaskCodePreprocessor(props.teams, props.tasks);
        preprocessor.getTeam(values.code);
        preprocessor.getTask(values.code);
    } catch (e) {
        errors.code = e.message;
    }
    return errors;
};

import { OwnProps } from './Components/main-form';
import { FormErrors } from 'redux-form';
import TaskCodePreprocessor from 'FKSDB/Components/Game/Submits/task-code-preprocessor';

export const validate = (values: { code?: string }, {tasks,teams}: OwnProps): FormErrors<{ code?: string }> => {
    const errors: FormErrors<{ code?: string }> = {};

    if (!values.code) {
        errors.code = 'Code is empty.';
        return errors;
    }
    try {
        const preprocessor = new TaskCodePreprocessor(teams, tasks);
        preprocessor.getTeam(values.code);
        preprocessor.getTask(values.code);
    } catch (e) {
        errors.code = e.message;
    }
    return errors;
};

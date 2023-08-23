import { SubmitFormRequest, submitStart } from 'FKSDB/Components/Game/Submits/Form/actions';
import Buttons from './buttons';
import Code from './code';
import ValueDisplay from './preview';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/nette-actions';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/task-model';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/team-model';
import * as React from 'react';
import { useContext } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Field, Form, formValueSelector, InjectedFormProps, reduxForm } from 'redux-form';
import { validate } from '../middleware';
import AutoButton from 'FKSDB/Components/Game/Submits/Form/Components/auto-button';
import { TranslatorContext } from '@translator/context';
import { Store } from 'FKSDB/Components/Controls/Upload/AjaxSubmit/Reducers';

export interface OwnProps {
    tasks: TaskModel[];
    teams: TeamModel[];
    actions: NetteActions;
    availablePoints: number[] | null;
}

function MainForm({
                      valid,
                      submitting,
                      handleSubmit,
                      tasks,
                      teams,
                      availablePoints,
                      actions,
                  }: OwnProps & InjectedFormProps<{ code: string }, OwnProps>) {
    const translator = useContext(TranslatorContext);
    const dispatch = useDispatch();
    const onSubmit = (values: SubmitFormRequest) => submitStart(dispatch, values, actions.getAction('save'))
    const code = useSelector((state: Store) => formValueSelector(FORM_NAME)(state, 'code'));
    const messages = useSelector((state: Store) => state.fetch.messages);

    const hasButtons = availablePoints.length;
    return <Form
        onSubmit={handleSubmit(onSubmit)}
        onKeyPress={(event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        }}>
        {messages.map((message, key) => {
            return <div key={key} className={'alert alert-' + message.level}> {message.text}</div>;
        })}
        <div className="offset-lg-3 col-lg-6 col-md-12">
            <div className="row mb-3">
                <h3>{translator.getText('Code')}</h3>
                <div className="form-group">
                    <Field name="code" component={Code}/>
                </div>
            </div>
            <div className="row mb-3">
                {hasButtons ?
                    <Buttons
                        availablePoints={availablePoints}
                        valid={valid}
                        submitting={submitting}
                        handleSubmit={handleSubmit}
                        onSubmit={onSubmit}
                    /> :
                    <AutoButton
                        valid={valid}
                        submitting={submitting}
                        handleSubmit={handleSubmit}
                        onSubmit={onSubmit}
                    />
                }
            </div>
            <ValueDisplay code={code} tasks={tasks} teams={teams}/>
        </div>
    </Form>;
}

export const FORM_NAME = 'codeForm';

export default reduxForm<{ code: string }, OwnProps, string>({
    form: FORM_NAME,
    validate,
})(MainForm);

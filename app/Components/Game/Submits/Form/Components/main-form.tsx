import { SubmitFormRequest, submitStart } from 'FKSDB/Components/Game/Submits/Form/actions';
import Code from './code';
import ValueDisplay from './preview';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/nette-actions';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/task-model';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/team-model';
import * as React from 'react';
import { Fragment, useContext } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Field, Form, formValueSelector, InjectedFormProps, reduxForm, change } from 'redux-form';
import { validate } from '../middleware';
import { TranslatorContext } from '@translator/context';
import { Store } from 'FKSDB/Components/Controls/Upload/AjaxSubmit/Reducers';

export interface OwnProps {
    tasks: TaskModel[];
    teams: TeamModel[];
    actions: NetteActions;
    availablePoints: number[] | null;
}

function MainForm({
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
    const points = useSelector((state: Store) => formValueSelector(FORM_NAME)(state, 'points'));
    const messages = useSelector((state: Store) => state.fetch.messages);

    const hasPoints = availablePoints.length;

    const options = availablePoints.map((value, index) => {
        return <Fragment key={index}>
            <Field
                name='points'
                component='input'
                type='radio'
                className='btn-check'
                id={'radio-' + index}
                value={value.toString()}
                checked={value == points}
                onChange={() => document.getElementById('codeInput').focus()}
                />
            <label className={'btn btn-lg ' + (points != null ? 'btn-outline-success' : 'btn-outline-secondary')} htmlFor={'radio-' + index}>
                {submitting
                    ? (<i className="fas fa-spinner fa-spin" aria-hidden="true"/>)
                    : (value + '. ' + translator.nGetText('point', 'points', value))
                }
            </label>
            </Fragment>;
    });

    return <Form
        onSubmit={handleSubmit(onSubmit)}
        onKeyPress={(event) => {
            if (points == null && hasPoints) {
                event.preventDefault();
                const selectedPoints: number = parseInt(event.key);
                if (availablePoints.includes(selectedPoints)) {
                    dispatch(change(FORM_NAME, 'points', selectedPoints));
                }
            }
        }}>
        {messages.map((message, key) => {
            return <div key={key} className={'alert alert-' + message.level}> {message.text}</div>;
        })}
        <div className="offset-lg-3 col-lg-6 col-md-12">
            {hasPoints ? <div className="row mb-3">
                <h3>{translator.getText('Points')}</h3>
                <div className='d-flex justify-content-around'>
                    {options}
                </div>
            </div> : ''}
            <div className="row mb-3">
                <h3>{translator.getText('Code')}</h3>
                <div className="form-group">
                    <Field name="code" component={Code}/>
                </div>
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

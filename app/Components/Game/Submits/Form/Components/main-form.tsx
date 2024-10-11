import { SubmitFormRequest, submitStart } from 'FKSDB/Components/Game/Submits/Form/actions';
import Buttons from './buttons';
import Code from './code';
import ValueDisplay from './preview';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/nette-actions';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/task-model';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/team-model';
import * as React from 'react';
import { useContext, useRef } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Field, Form, formValueSelector, InjectedFormProps, reduxForm } from 'redux-form';
import { validate } from '../middleware';
import AutoButton from 'FKSDB/Components/Game/Submits/Form/Components/auto-button';
import { TranslatorContext } from '@translator/context';
import { Store } from 'FKSDB/Components/Upload/AjaxSubmit/Reducers';

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

    const buttonRef = useRef(null);

    function getButtonMap(): Map<number, HTMLElement> {
        if (!buttonRef.current) {
            // initialize map on first usage
            buttonRef.current = new Map()
        }

        return buttonRef.current;
    }

    function addButtonRefToMap(index: number, node: HTMLElement) {
        const map: Map<number, HTMLElement> = getButtonMap();
        if (node) {
            map.set(index, node);
        } else {
            map.delete(index);
        }
    }

    return <Form
        onSubmit={handleSubmit(onSubmit)}
        onKeyPress={(event) => {
            if (event.key === 'Enter' && hasButtons) {
                event.preventDefault();
            }

            if (valid && hasButtons) {
                const selectedPoints: number = parseInt(event.key);
                if (!isNaN(selectedPoints) && availablePoints.includes(selectedPoints)) {
                    event.preventDefault();
                    document.getElementById("pointsButton-" + selectedPoints).click();
                    const map = getButtonMap();
                    const buttonNode = map.get(selectedPoints);
                    buttonNode.click();
                }
            }
        }}>
        {messages.map((message, key) => {
            return <div key={key} className={'alert alert-' + message.level} dangerouslySetInnerHTML={{__html: message.text}}/>;
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
                        refCallback={addButtonRefToMap}
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

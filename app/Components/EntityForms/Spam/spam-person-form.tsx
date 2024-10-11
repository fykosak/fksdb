import * as React from 'react';
import { useContext } from 'react';
import { Field, Form, InjectedFormProps, reduxForm } from 'redux-form';
import {SpamPersonFormRequest, submitStart} from './actions';
import {NetteActions} from 'vendor/fykosak/nette-frontend-component/src/NetteActions/nette-actions';
import {useDispatch, useSelector} from 'react-redux';
import { TranslatorContext } from '@translator/context';
import { Store } from 'FKSDB/Components/Upload/AjaxSubmit/Reducers';

export interface OwnProps {
    actions: NetteActions,
    studyYears: Map<string, Map<string, string>>
    acYear: number
}

function SpamPersonForm({
            handleSubmit,
            submitting,
            studyYears,
            acYear,
            actions
        }: OwnProps & InjectedFormProps<{ name: string }, OwnProps>
    ) {

    const translator = useContext(TranslatorContext);
    const dispatch = useDispatch();
    const onSubmit = (values: SpamPersonFormRequest) => submitStart(dispatch, values, actions.getAction('save'));
    const messages = useSelector((state: Store) => state.fetch.messages);

    // JSON is not able to decode the map and it decodes it as Object.
    // For that reason, and to be able to use map, convert studyYears
    // into an array of [key, value] arrays.
    const studyYearOptions = [
        <option key="default" hidden disabled value=''>{translator.getText('Choose study year')}</option>
    ];
    studyYearOptions.push(...Object.entries(studyYears).map(([group_key, group_values]) =>
    <optgroup key={group_key} label={group_key}>
        {Object.entries(group_values).map(([item_key, item_value]) =>
            <option key={item_key} value={item_key}>{item_value}</option>
        )}
    </optgroup>
    ));

    return <Form onSubmit={handleSubmit(onSubmit)}>
        <div className="row mb-3">
            <div className="form-group col-12 col-lg-6 mb-3 mb-lg-0 required">
                <label htmlFor="input-other-name" className="form-label">{translator.getText('Other name')}</label>
                <Field name="other_name" id="input-other-name" className="form-control" component="input" type="text" required autoFocus/>
            </div>
            <div className="form-group col-12 col-lg-6 required">
                <label htmlFor="input-family-name" className="form-label">{translator.getText('Family name')}</label>
                <Field name="family_name" id="input-family-name"className="form-control" component="input" type="text" required/>
            </div>
        </div>
        <div className="form-group mb-3 required">
            <label htmlFor="input-school-label" className="form-label">{translator.getText('School label')}</label>
            <Field name="school_label_key" id="input-school-label"className="form-control" component="input" type="text" required/>
        </div>
        <div className="row mb-3">
            <div className="form-group col-12 col-lg-6 mb-3 mb-lg-0 required">
                <label htmlFor="input-study_year" className="form-label">{translator.getText('Study year')}</label>
                <Field name="study_year_new" id="input-study-year" className="form-control" component="select" required>
                    {studyYearOptions}
                </Field>
            </div>
            <div className="form-group col-12 col-lg-6">
                <label htmlFor="input-ac-year" className="form-label">{translator.getText('Academic year')}</label>
                <div id="input-ac-year" className="form-control text-muted">{acYear}/{acYear+1}</div>
                <span className="form-text">{translator.getText('Select different contest year to change this value')}</span>
            </div>
        </div>
        <button type="submit" className="btn btn-outline-primary mb-3">
            {submitting ? <i className="fas fa-spinner fa-spin" aria-hidden="true"/> : translator.getText('Create')}
        </button>
        {messages.map((message, key) => {
            return <div key={key} className={'alert alert-' + message.level} dangerouslySetInnerHTML={{__html: message.text}}/>;
        })}
    </Form>
}

export const FORM_NAME = 'personForm';

export default reduxForm<{ name: string }, OwnProps, string>({
    form: FORM_NAME
})(SpamPersonForm);

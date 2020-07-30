import {
    Message,
    Response2,
} from '@fetchApi/interfaces';
import { lang } from '@i18n/i18n';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Field,
    formValueSelector,
    SubmitHandler,
} from 'redux-form';
import {
    Task,
    Team,
} from '../../helpers/interfaces';
import { SubmitFormRequest } from '../actions';
import { Store as SubmitStore } from '../reducers';
import { FORM_NAME } from './container';
import ScanInput from './inputs/scan';
import SubmitButtons from './inputs/submitButtons';
import TextInput from './inputs/text';
import ErrorBlock from './outputs/errorBlock';
import ValueDisplay from './outputs/valueDisplay';

export interface OwnProps {
    accessKey: string;
    tasks: Task[];
    teams: Team[];
    valid: boolean;
    submitting: boolean;
    availablePoints: number[];
    handleSubmit: SubmitHandler<{ code: string }, any>;

    onSubmit(values: SubmitFormRequest): Promise<Response2<void>>;
}

interface StateProps {
    code: string;
    messages: Message[];
}

class FormSection extends React.Component<OwnProps & StateProps, {}> {

    public render() {
        const {valid, submitting, handleSubmit, onSubmit, code, tasks, teams, messages, availablePoints} = this.props;

        return (
            <>
                {messages.map((message, key) => {
                    return <div key={key} className={'alert alert-' + message.level}> {message.text}</div>;
                })}
                <div className="row">
                    <div className="col-lg-6 col-md-12 mb-3">
                        <h3 className={'fyziklani-headline-color'}>{lang.getText('Code')}</h3>
                        <div className="form-group">
                            <Field name="code" component={TextInput}/>
                        </div>
                        <div className="form-group">
                            <Field name="code" component={ErrorBlock}/>
                        </div>
                    </div>
                    <div className="col-lg-6 col-md-12 mb-3">
                        <Field name="code" component={ScanInput}/>
                    </div>

                    <div className="col-12">
                        <SubmitButtons
                            availablePoints={availablePoints}
                            valid={valid}
                            submitting={submitting}
                            handleSubmit={handleSubmit}
                            onSubmit={onSubmit}/>
                    </div>

                </div>
                <hr/>
                <ValueDisplay code={code} tasks={tasks} teams={teams}/>
            </>
        );
    }
}

const mapStateToProps = (state: SubmitStore, ownProps: OwnProps): StateProps => {
    const selector = formValueSelector(FORM_NAME);
    const {accessKey} = ownProps;
    return {
        code: selector(state, 'code'),
        messages: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].messages : [],
    };
};

export default connect(mapStateToProps, null)(FormSection);

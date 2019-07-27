import {
    Message,
    Response,
} from '@fetchApi/middleware/interfaces';
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
} from '../../helpers/interfaces/';
import { SubmitFormRequest } from '../actions';
import { Store as SubmitStore } from '../reducers/';
import CodeInputError from './ErrorBlock';
import { FORM_NAME } from './FormContainer';
import CodeInput from './Input';
import SubmitButtons from './SubmitButtons';
import ValueDisplay from './ValueDisplay';

export interface Props {
    accessKey: string;
    tasks: Task[];
    teams: Team[];
    valid: boolean;
    submitting: boolean;
    availablePoints: number[];
    handleSubmit: SubmitHandler<{ code: string }, any, string>;

    onSubmit?(values: SubmitFormRequest): Promise<Response<void>>;
}

interface State {
    code?: string;
    messages?: Message[];
}

class FormSection extends React.Component<Props & State, {}> {

    public render() {
        const {valid, submitting, handleSubmit, onSubmit, code, tasks, teams, messages, availablePoints} = this.props;

        return (
            <div>
                {messages.map((message, key) => {
                    return <div key={key} className={'alert alert-' + message.level}> {message.text}</div>;
                })}
                <div className="row">
                    <div className="col-6">
                        <h3 className={'fyziklani-headline-color'}>Task's code</h3>

                        <div className="form-group">
                            <Field name="code" component={CodeInput}/>
                        </div>
                        <div className="form-group">
                            <Field name="code" component={CodeInputError}/>
                        </div>
                        <SubmitButtons
                            availablePoints={availablePoints}
                            valid={valid}
                            submitting={submitting}
                            handleSubmit={handleSubmit}
                            onSubmit={onSubmit}/>
                    </div>
                    <div className="col-6">
                        <ValueDisplay code={code} tasks={tasks} teams={teams}/>
                    </div>
                </div>
            </div>
        );
    }
}

const mapStateToProps = (state: SubmitStore, ownProps: Props): State => {
    const selector = formValueSelector(FORM_NAME);
    const {accessKey} = ownProps;
    return {
        code: selector(state, 'code'),
        messages: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].messages : [],
    };
};

export default connect(mapStateToProps, null)(FormSection);

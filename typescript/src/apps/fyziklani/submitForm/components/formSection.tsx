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
import CodeInputError from './errorBlock';
import { FORM_NAME } from './formContainer';
import CodeInput from './input';
import SubmitButtons from './submitButtons';
import ValueDisplay from './valueDisplay';
import Scan from './scan';

export interface OwnProps {
    accessKey: string;
    tasks: Task[];
    teams: Team[];
    valid: boolean;
    submitting: boolean;
    availablePoints: number[];
    handleSubmit: SubmitHandler<{ code: string }, any, string>;

    onSubmit(values: SubmitFormRequest): Promise<Response<void>>;
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
                        <h3 className={'fyziklani-headline-color'}>Task's code</h3>
                        <div className="form-group">
                            <Field name="code" component={CodeInput}/>
                        </div>
                        <div className="form-group">
                            <Field name="code" component={CodeInputError}/>
                        </div>
                    </div>
                    <div className="col-lg-6 col-md-12 mb-3">
                        <Field name="code" component={Scan}/>
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

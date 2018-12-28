import * as React from 'react';
import { connect } from 'react-redux';
import {
    Field,
    formValueSelector,
    SubmitHandler,
} from 'redux-form';
import {
    IMessage,
    IResponse,
} from '../../../fetch-api/middleware/interfaces';
import {
    ITask,
    ITeam,
} from '../../helpers/interfaces/';
import { ISubmitFormRequest } from '../actions';
import { IFyziklaniSubmitStore } from '../reducers/';
import CodeInputError from './error-block';
import { FORM_NAME } from './form-container';
import CodeInput from './input';
import SubmitButtons from './submit-buttons';
import ValueDisplay from './value-display';

export interface IProps {
    accessKey: string;
    tasks: ITask[];
    teams: ITeam[];
    valid: boolean;
    submitting: boolean;
    availablePoints: number[];
    handleSubmit: SubmitHandler<{ code: string }, any, string>;

    onSubmit?(values: ISubmitFormRequest): Promise<IResponse<void>>;
}

interface IState {
    code?: string;
    messages?: IMessage[];
}

class FormSection extends React.Component<IProps & IState, {}> {

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

const mapStateToProps = (state: IFyziklaniSubmitStore, ownProps: IProps): IState => {
    const selector = formValueSelector(FORM_NAME);
    const {accessKey} = ownProps;
    return {
        code: selector(state, 'code'),
        messages: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].messages : [],
    };
};

export default connect(mapStateToProps, null)(FormSection);

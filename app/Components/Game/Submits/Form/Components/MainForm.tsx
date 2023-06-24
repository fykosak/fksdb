import { SubmitFormRequest, submitStart } from 'FKSDB/Components/Game/Submits/Form/actions';
import Buttons from './Buttons';
import Code from './Code';
import ValueDisplay from './Preview';
import { Store as SubmitStore } from 'FKSDB/Components/Game/Submits/Form/reducer';
import { DataResponse, Message } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';
import { Field, Form, formValueSelector, InjectedFormProps, reduxForm } from 'redux-form';
import { validate } from '../middleware';
import AutoButton from 'FKSDB/Components/Game/Submits/Form/Components/AutoButton';
import { TranslatorContext } from '@translator/LangContext';

export interface OwnProps {
    tasks: TaskModel[];
    teams: TeamModel[];
    actions: NetteActions;
    availablePoints: number[] | null;
}

interface DispatchProps {
    onSubmit(values: SubmitFormRequest): Promise<DataResponse<SubmitFormRequest>>;
}

interface StateProps {
    code: string;
    messages: Message[];
}

class MainForm extends React.Component<StateProps & OwnProps & DispatchProps & InjectedFormProps<{ code: string }, OwnProps>, never> {
    static contextType = TranslatorContext;

    public render() {
        const translator = this.context;
        const {valid, submitting, handleSubmit, onSubmit, tasks, teams, availablePoints, messages, code} = this.props;
        const hasButtons = availablePoints.length;
        return (
            <Form
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
                    <hr/>
                    <ValueDisplay code={code} tasks={tasks} teams={teams}/>
                </div>
            </Form>
        );
    }
}

export const FORM_NAME = 'codeForm';

const mapDispatchToProps = (dispatch: Dispatch<Action>, ownProps: OwnProps): DispatchProps => {
    return {
        onSubmit: (values: SubmitFormRequest) => submitStart(dispatch, values, ownProps.actions.getAction('save')),
    };
};

const mapStateToProps = (state: SubmitStore): StateProps => {
    const selector = formValueSelector(FORM_NAME);
    return {
        code: selector(state, 'code'),
        messages: state.fetch.messages,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(
    reduxForm<{ code: string }, OwnProps, string>({
        form: FORM_NAME,
        validate,
    })(MainForm),
);

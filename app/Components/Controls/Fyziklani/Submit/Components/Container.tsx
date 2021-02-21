import { translator } from '@translator/translator';
import { SubmitFormRequest, submitStart } from 'FKSDB/Components/Controls/Fyziklani/Submit/actions';
import ScanInput from 'FKSDB/Components/Controls/Fyziklani/Submit/Components/Inputs/ScanInput';
import SubmitButtons from 'FKSDB/Components/Controls/Fyziklani/Submit/Components/Inputs/SubmitButtons';
import TextInput from 'FKSDB/Components/Controls/Fyziklani/Submit/Components/Inputs/TextInput';
import ErrorBlock from 'FKSDB/Components/Controls/Fyziklani/Submit/Components/Outputs/ErrorBlock';
import ValueDisplay from 'FKSDB/Components/Controls/Fyziklani/Submit/Components/Outputs/ValueDisplay';
import { Store as SubmitStore } from 'FKSDB/Components/Controls/Fyziklani/Submit/reducer';
import { Message } from 'FKSDB/Models/FrontEnd/Fetch/interfaces';
import { NetteActions } from 'FKSDB/Models/FrontEnd/Loader/netteActions';
import { ModelFyziklaniTask } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTask';
import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';
import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';
import { Field, Form, formValueSelector, InjectedFormProps, reduxForm } from 'redux-form';
import { validate } from '../middleware';

export interface OwnProps {
    tasks: ModelFyziklaniTask[];
    teams: ModelFyziklaniTeam[];
    actions: NetteActions;
    availablePoints: number[];
}

interface DispatchProps {
    onSubmit(values: SubmitFormRequest): Promise<any>;
}

interface StateProps {
    code: string;
    messages: Message[];
}

class Container extends React.Component<StateProps & OwnProps & DispatchProps & InjectedFormProps<{ code: string }, OwnProps>> {

    public render() {
        const {valid, submitting, handleSubmit, onSubmit, tasks, teams, availablePoints, messages, code} = this.props;

        return (
            <Form onSubmit={handleSubmit(onSubmit)}>
                {messages.map((message, key) => {
                    return <div key={key} className={'alert alert-' + message.level}> {message.text}</div>;
                })}
                <div className="row">
                    <div className="col-lg-6 col-md-12 mb-3">
                        <h3 className={'fyziklani-headline-color'}>{translator.getText('Code')}</h3>
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
        messages: state.fetchApi.messages,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(
    reduxForm<{ code: string }, any, string>({
        form: FORM_NAME,
        validate,
    })(Container),
);

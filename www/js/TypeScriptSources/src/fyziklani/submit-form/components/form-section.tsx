import * as React from 'react';
import { connect } from 'react-redux';
import {
    Field,
    formValueSelector,
} from 'redux-form';
import { IMessage } from '../../../fetch-api/middleware/interfaces';
import Card from '../../../shared/components/card';
import {
    ITask,
    ITeam,
} from '../../helpers/interfaces/';
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
    handleSubmit: (args: any) => any;
    availablePoints: number[];

    onSubmit(values: any): Promise<any>;
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
                        <Card level="info" headline="Task's code">
                            <div className="form-inline">
                                <Field name="code" component={CodeInput}/>
                            </div>
                            <div className="form-inline">
                                <Field name="code" component={CodeInputError}/>
                            </div>
                            <SubmitButtons availablePoints={availablePoints}
                                           valid={valid}
                                           submitting={submitting}
                                           handleSubmit={handleSubmit}
                                           onSubmit={onSubmit}/>
                        </Card>
                    </div>
                    <div className="col-6">
                        <Card level="info" headline="Display">
                            <ValueDisplay code={code} tasks={tasks} teams={teams}/>
                        </Card>
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

export default connect(mapStateToProps, (): IState => {
    return {};
})(FormSection);

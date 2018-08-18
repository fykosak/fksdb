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
    tasks: ITask[];
    teams: ITeam[];
    onSubmit: (values: any) => Promise<any>;
    valid: boolean;
    submitting: boolean;
    handleSubmit: any;
}

interface IState {
    code?: string;
    msg?: IMessage[];
}

class FormSection extends React.Component<IProps & IState, {}> {

    public render() {
        const {valid, submitting, handleSubmit, onSubmit, code, tasks, teams, msg} = this.props;

        return (
            <div>
                {msg && (<div className={'alert alert-' + msg[1]}> {msg[0]}</div>)}
                <div className="row">
                    <div className="col-6">
                        <Card level="info" headline="Task's code">
                            <div className="form-inline">
                                <Field name="code" component={CodeInput}/>
                            </div>
                            <div className="form-inline">
                                <Field name="code" component={CodeInputError}/>
                            </div>
                            <SubmitButtons valid={valid} submitting={submitting} handleSubmit={handleSubmit} onSubmit={onSubmit}/>
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

const mapStateToProps = (state: IFyziklaniSubmitStore): IState => {
    const selector = formValueSelector(FORM_NAME);
    const accessKey = '@@fyziklani';
    return {
        code: selector(state, 'code'),
        msg: state.fetchApi[accessKey].messages,
    };
};

export default connect(mapStateToProps, (): IState => {
    return {};
})(FormSection);

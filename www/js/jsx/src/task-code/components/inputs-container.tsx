import * as React from 'react';
import {
    connect,
} from 'react-redux';
import {
    Form,
    formValueSelector,
    reduxForm,
} from 'redux-form';
import { validate } from '../middleware/form';
import {
    ITask,
    ITeam,
} from '../middleware/interfaces';
import { IStore } from '../reducers/index';

import FocusSwitcher from './focus-switcher';
import ByCodeContainer from './inputs-container/by-code-container';
import SelectContainer from './inputs-container/select-container';
import SubmitButtons from './inputs-container/submit-buttons';

export interface IProps {
    tasks: ITask[];
    teams: ITeam[];
    onSubmit?: (values: any) => Promise<any>;
}

interface IState {
    team?: string;
    task?: string;
    control?: string;
    msg?: string[];
}

class InputsContainer extends React.Component<IProps & IState & any, {}> {

    public render(): JSX.Element {
        const { teams, tasks, valid, submitting, handleSubmit, onSubmit, msg } = this.props;

        return (
            <div className="task-code-container">
                {msg && (<div className={'alert alert-' + msg[1]}> {msg[0]}</div>)}
                <Form className="row" onSubmit={handleSubmit(onSubmit) }>
                    <div className="col-6">
                        <ByCodeContainer/>
                    </div>
                    <div className="col-6">
                        <SubmitButtons valid={valid} submitting={submitting} handleSubmit={handleSubmit} onSubmit={onSubmit}/>
                    </div>
                    <div className="col-6 mt-3">
                        <SelectContainer teams={teams} tasks={tasks}/>
                    </div>
                    <FocusSwitcher />
                </Form>
            </div>
        );
    }

}

export const FORM_NAME = 'codeForm';
const mapStateToProps = (state: IStore): IState => {
    const selector = formValueSelector(FORM_NAME);
    return {
        ...selector(state, 'task', 'team', 'control'),
        msg: state.submit.msg,
    };
};

export default connect(mapStateToProps, (): IState => {
    return {};
})(reduxForm({
    form: FORM_NAME,
    validate,
})(InputsContainer));

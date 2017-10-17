import * as React from 'react';

import {
    connect,
} from 'react-redux';
import {
    ITask,
    ITeam,
} from '../middleware/interfaces';
import { IStore } from '../reducers/index';

import ControlInput from './control-input';
// import FocusSwitcher from './focus-switcher';
import TaskInput from './task-input';
import TeamInput from './team-input';

import {
    Field,
    Form,
    formValueSelector,
    reduxForm,
} from 'redux-form';

interface IProps {
    tasks: ITask[];
    teams: ITeam[];
}

interface IState {
    team?: string;
    task?: string;
    control?: string;
}
// <FocusSwitcher />
class InputsContainer extends React.Component<IProps & IState & any, {}> {

    public render(): JSX.Element {
        const { valid } = this.props;

        return (
            <div className="task-code-container">
                <Form className="has-feedback form-inline">
                    <Field name="team" component={TeamInput}/>
                    <Field name="task" component={TaskInput}/>
                    <Field name="control" component={ControlInput}/>
                    <span
                        className={'glyphicon ' + (valid ? 'glyphicon-ok' : '') + ' form-control-feedback'}
                    />

                </Form>
                <div className="clearfix"/>
                {this.getValueDisplay()}
            </div>
        );
    }

    private getValueDisplay(): JSX.Element {
        const { task, team, teams, tasks } = this.props;
        const elements = [];

        const filterTeams = teams.filter((currentTeam) => {
            return currentTeam.team_id === +team;
        });
        elements.push(<div className="form-group">
            <label className="col-sm-2 control-label">Tým:</label>
            <div className="col-sm-10">
                <p className="form-control-static">{filterTeams.length ? filterTeams[0].name : '-'}</p>
            </div>
        </div>);

        const filterTasks = tasks.filter((currentTask) => {
            return task && currentTask.label === task.toUpperCase();
        });
        elements.push(<div className="form-group">
            <label className="col-sm-2 control-label">Úloha:</label>
            <div className="col-sm-10">
                <p className="form-control-static">{filterTasks.length ? filterTasks[0].name : '-'}</p>
            </div>
        </div>);

        return (<div className="clearer">{elements}</div>);
    }

}

const getFullCode = (values): string => {
    const teamString = (+values.team < 1000) ? '0' + +values.team : +values.team;
    return '00' + teamString + values.task + values.control;
};

const isValidFullCode = (code: string): boolean => {

    const subCode = code.split('').map((char): number => {
        return +char.toLocaleUpperCase()
            .replace('A', '1')
            .replace('B', '2')
            .replace('C', '3')
            .replace('D', '4')
            .replace('E', '5')
            .replace('F', '6')
            .replace('G', '7')
            .replace('H', '8');
    });
    return (getControl(subCode) % 10 === 0);
};

const getControl = (subCode: Array<string | number>): number => {
    return (+subCode[0] + +subCode[3] + +subCode[6]) * 3 +
        (+subCode[1] + +subCode[4] + +subCode[7]) * 7 +
        (+subCode[2] + +subCode[5] + +subCode[8]);
};

const validate = (values, props: IProps) => {
    const errors: any = {};
    if (values.team) {
        const teams = props.teams.filter((currentTeam) => {
            return currentTeam.team_id === +values.team;
        });
        if (!teams.length) {
            errors.team = { type: 'danger', msg: 'team neexistuje' };
        }
    }
    if (values.task) {
        const tasks = props.tasks.filter((currentTask) => {
            return currentTask.label === values.task.toUpperCase();
        });
        if (!tasks.length) {
            errors.task = { type: 'danger', msg: 'úloha neexistuje' };
        }
    }
    if ((!errors.task && !errors.team && values.control)) {
        const code = getFullCode(values);
        if (!isValidFullCode(code)) {
            errors.control = { type: 'danger', msg: 'Neplatný control' };
        }
    }
    if (!values.hasOwnProperty('control') || values.control === '') {
        errors.control = { type: 'danger', msg: 'Required' };
    }
    if (!values.hasOwnProperty('team') || !values.team) {
        errors.team = { type: 'danger', msg: 'Required' };
    }
    if (!values.hasOwnProperty('task') || !values.task) {
        errors.task = { type: 'danger', msg: 'Required' };
    }
    return errors;
};

const FORM_NAME = 'codeForm';
const mapStateToProps = (state: IStore) => {
    const selector = formValueSelector(FORM_NAME);
    return selector(state, 'task', 'team', 'control');
};

export default connect(mapStateToProps, (): IState => {
    return {};
})(reduxForm({
    form: FORM_NAME,
    validate,
})(InputsContainer));


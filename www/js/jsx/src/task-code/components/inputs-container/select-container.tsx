import * as React from 'react';

import ControlInput from '../inputs/control-input';
import ControlInputError from '../inputs/control-input-error';

import {
    Field,
} from 'redux-form';
import {
    ITask,
    ITeam,
} from '../../middleware/interfaces';

interface IProps {
    teams: ITeam[];
    tasks: ITask[];
}

export default class SelectContainer extends React.Component<IProps, {}> {
    public render() {
        const { teams, tasks } = this.props;
        const optionsTeams = teams.map((currentTeam: ITeam, index) => {
            return (<option key={index} value={currentTeam.team_id}>{currentTeam.name}</option>);
        });
        optionsTeams.unshift((<option key="null" value="">--</option>));

        const optionsTasks = tasks.map((currentTask: ITask, index) => {
            return (<option key={index} value={currentTask.label}>{currentTask.name}</option>);
        });
        optionsTasks.unshift((<option key="null" value="">--</option>));

        return (<div className="card card-outline-info">
            <div className="card-header card-info">Výber týmu a úlohy</div>
            <div className="card-block">
                <div className="form-group row">
                    <label className="col-sm-2 col-form-label">Tým:</label>
                    <div className="col-sm-10">
                        <Field name="team" component="select" className="form-control">{optionsTeams}</Field>
                    </div>
                </div>
                <div className="form-group row">
                    <label className="col-sm-2 col-form-label">Úloha:</label>
                    <div className="col-sm-10">
                        <Field name="task" component="select" className="form-control">{optionsTasks}</Field>
                    </div>
                </div>
                <div className="form-group row">
                    <label className="col-sm-2 col-form-label">Control:</label>
                    <div className="col-sm-10">
                        <Field name="control" component={ControlInput} noRefMode={true}/>
                        <Field name="control" component={ControlInputError}/>
                    </div>
                </div>
            </div>
        </div>);
    }
}

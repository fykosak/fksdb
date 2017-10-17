import * as React from 'react';

import ControlInput from '../inputs/control-input';
import TaskInput from '../inputs/task-input';
import TeamInput from '../inputs/team-input';

import ControlInputError from '../inputs/control-input-error';
import TaskInputError from '../inputs/task-input-error';
import TeamInputError from '../inputs/team-input-error';

import {
    Field,
} from 'redux-form';

export default class InputContainerByCode extends React.Component<{}, {}> {
    public render() {

        return (<div className="card card-outline-info">
            <div className="card-header card-info">Zadáni podle kódu</div>
            <div className="card-block">
                <div className="form-inline">
                    <Field name="team" component={TeamInput}/>
                    <Field name="task" component={TaskInput} normalize={(value) => value.toUpperCase()}/>
                    <Field name="control" component={ControlInput}/>
                </div>
                <div className="form-inline">
                    <Field name="team" component={TeamInputError}/>
                    <Field name="task" component={TaskInputError}/>
                    <Field name="control" component={ControlInputError}/>
                </div>
            </div>
        </div>);
    }
}

import * as React from 'react';

import {
    connect,
} from 'react-redux';

import { IStore } from '../reducers/index';
import { FORM_NAME } from './inputs-container';

interface IState {
    taskInput?: HTMLInputElement;
    teamInput?: HTMLInputElement;
    controlInput?: HTMLInputElement;
    errors?: { [key: string]: any };
}

class InputsContainer extends React.Component<IState, {}> {

    public componentDidUpdate() {

        const { errors, taskInput, controlInput, teamInput } = this.props;
        if (errors.team) {
            teamInput.focus();
        }
        if (!errors.team && errors.task) {
            taskInput.focus();
        }
        if (!errors.team && !errors.task) {
            controlInput.focus();
        }
    }

    public render() {
        return null;
    }

}

const mapStateToProps = (state: IStore): IState => {
    return {
        controlInput: state.nodes.controlInput,
        errors: state.form[FORM_NAME].syncErrors,
        taskInput: state.nodes.taskInput,
        teamInput: state.nodes.teamInput,
    };
};

export default connect(mapStateToProps, null)(InputsContainer);

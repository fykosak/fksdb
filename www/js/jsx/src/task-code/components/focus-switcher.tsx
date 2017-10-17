import * as React from 'react';

import {
    connect,
} from 'react-redux';

import { IStore } from '../reducers/index';

interface IState {
    taskInput?: HTMLInputElement;
    teamInput?: HTMLInputElement;
    controlInput?: HTMLInputElement;
    validTask?: boolean;
    validTeam?: boolean;
}

class InputsContainer extends React.Component<IState, {}> {

    public componentDidUpdate() {

        const { validTeam, validTask, taskInput, controlInput, teamInput } = this.props;
        if (!validTeam) {
            teamInput.focus();
        }
        if (validTeam && !validTask) {
            taskInput.focus();
        }
        if (validTeam && validTask) {
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
        taskInput: state.nodes.taskInput,
        teamInput: state.nodes.teamInput,
        validTask: state.code.taskCode.valid,
        validTeam: state.code.teamCode.valid,
    };
};

export default connect(mapStateToProps, null)(InputsContainer);


import * as React from 'react';

import {
    connect,
    Dispatch,
} from 'react-redux';
import { saveTeams } from '../actions/save';
import { ITeam } from '../interfaces';
import { IStore } from '../reducers/index';

interface IState {
    onSaveRouting?: (teams: ITeam[]) => void;
    teams?: ITeam[];
}
class Form extends React.Component<IState, {}> {

    public render() {
        const { onSaveRouting, teams } = this.props;
        return (<button className="btn btn-success" onClick={() => {
            onSaveRouting(teams);
        }}>Save
        </button>);
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onSaveRouting: (data) => dispatch(saveTeams(dispatch, data)),
    };
};

const mapStateToProps = (state: IStore): IState => {
    return {
        teams: state.teams,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Form);

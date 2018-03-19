import * as React from 'react';

import {
    connect,
    Dispatch,
} from 'react-redux';
import { ITeam } from '../../shared/interfaces';
import { saveTeams } from '../actions/save';
import { IStore } from '../reducers/index';

interface IState {
    onSaveRouting?: (teams: ITeam[]) => void;
    teams?: ITeam[];
    saving?: boolean;
    error?: any;
}

class Form extends React.Component<IState, {}> {

    public render() {
        const {onSaveRouting, teams, saving, error} = this.props;
        return (<div>
            <button disabled={saving} className="btn btn-success" onClick={() => {
                onSaveRouting(teams);
            }}>Save
            </button>
            {error && (<span className="text-danger">{error.statusText}</span>)}
        </div>);
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onSaveRouting: (data: ITeam[]) => saveTeams(dispatch, data),
    };
};

const mapStateToProps = (state: IStore): IState => {
    return {
        error: state.save.error,
        saving: state.save.saving,
        teams: state.teams,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Form);

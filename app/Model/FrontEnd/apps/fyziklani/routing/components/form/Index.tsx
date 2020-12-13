import { ModelFyziklaniTeam } from '@FKSDB/Model/ORM/Models/Fyziklani/modelFyziklaniTeam';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { saveTeams } from '../../actions/save';
import { Store as RoutingStore } from '../../reducers/';

interface StateProps {
    teams: ModelFyziklaniTeam[];
    saving: boolean;
    error: Error | any;
}

interface DispatchProps {
    onSaveRouting(teams: ModelFyziklaniTeam[]): void;
}

class Form extends React.Component<StateProps & DispatchProps, {}> {

    public render() {
        const {onSaveRouting, teams, saving, error} = this.props;
        return (<>
            <button disabled={saving} className="btn btn-success" onClick={() => {
                onSaveRouting(teams);
            }}>Save
            </button>
            {error && (<span className="text-danger">{error.statusText}</span>)}
        </>);
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action>): DispatchProps => {
    return {
        onSaveRouting: (data: ModelFyziklaniTeam[]) => saveTeams(dispatch, data),
    };
};

const mapStateToProps = (state: RoutingStore): StateProps => {
    return {
        error: state.fetchApi.error,
        saving: state.fetchApi.submitting,
        teams: state.teams.availableTeams,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Form);

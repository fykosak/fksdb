import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { Team } from '../../../helpers/interfaces/';
import { saveTeams } from '../../actions/save';
import { Store as RoutingStore } from '../../reducers/';
import jqXHR = JQuery.jqXHR;

interface StateProps {
    teams: Team[];
    saving: boolean;
    error: jqXHR<any>;
}

interface DispatchProps {
    onSaveRouting(teams: Team[]): void;
}

interface OwnProps {
    accessKey: string;
}

class Form extends React.Component<StateProps & DispatchProps & OwnProps, {}> {

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

const mapDispatchToProps = (dispatch: Dispatch<Action>, ownProps: OwnProps): DispatchProps => {
    const {accessKey} = ownProps;
    return {
        onSaveRouting: (data: Team[]) => saveTeams(accessKey, dispatch, data),
    };
};

const mapStateToProps = (state: RoutingStore, ownProps: OwnProps): StateProps => {
    const {accessKey} = ownProps;
    return {
        error: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].error : null,
        saving: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].submitting : false,
        teams: state.teams.availableTeams,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Form);

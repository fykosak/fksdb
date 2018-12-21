import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { ITeam } from '../../../helpers/interfaces/';
import { saveTeams } from '../../actions/save';
import { IFyziklaniRoutingStore } from '../../reducers/';
import jqXHR = JQuery.jqXHR;

interface IState {
    teams?: ITeam[];
    saving?: boolean;
    error?: jqXHR<any>;

    onSaveRouting?(teams: ITeam[]): void;
}

interface IProps {
    accessKey: string;
}

class Form extends React.Component<IState & IProps, {}> {

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

const mapDispatchToProps = (dispatch: Dispatch<Action>, ownProps: IProps): IState => {
    const {accessKey} = ownProps;
    return {
        onSaveRouting: (data: ITeam[]) => saveTeams(accessKey, dispatch, data),
    };
};

const mapStateToProps = (state: IFyziklaniRoutingStore, ownProps: IProps): IState => {
    const {accessKey} = ownProps;
    return {
        error: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].error : null,
        saving: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].submitting : false,
        teams: state.teams.availableTeams,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Form);

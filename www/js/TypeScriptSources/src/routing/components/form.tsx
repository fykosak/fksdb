import * as React from 'react';

import {
    connect,
    Dispatch,
} from 'react-redux';
import { dispatchNetteFetch } from '../../fetch-api/middleware/fetch';
import { IRequest } from '../../fetch-api/middleware/interfaces';
import { ITeam } from '../../shared/interfaces';
import { IStore } from '../reducers/';

interface IState {
    onSaveRouting?: (teams: IRequest<ITeam[]>, success: any, error: any) => void;
    teams?: ITeam[];
    saving?: boolean;
    error?: any;
}

class Form extends React.Component<IState, {}> {

    public render() {
        const {onSaveRouting, teams, saving, error} = this.props;
        return (<div>
            <button disabled={saving} className="btn btn-success" onClick={() => {
                onSaveRouting({act: 'save-brawl-routing', data: teams}, () => null, () => null);
            }}>Save
            </button>
            {error && (<span className="text-danger">{error.statusText}</span>)}
        </div>);
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onSaveRouting: (data: IRequest<ITeam[]>, success, error) =>
            dispatchNetteFetch<ITeam[], any, IStore>('brawl-routing', dispatch, data, success, error),
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

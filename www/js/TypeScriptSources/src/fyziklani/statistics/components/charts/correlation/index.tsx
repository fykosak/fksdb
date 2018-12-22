import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { lang } from '../../../../../i18n/i18n';
import { ITeam } from '../../../../helpers/interfaces';
import {
    setFirstTeamId,
    setSecondTeamId,
} from '../../../actions';
import { IFyziklaniStatisticsStore } from '../../../reducers';
import GlobalCorrelation from './global-correlation/chart';
import Table from './table/chart';

interface IState {
    teams?: ITeam[];
    firstTeamId?: number;
    secondTeamId?: number;

    onChangeFirstTeam?(id: number): void;

    onChangeSecondTeam?(id: number): void;

}

class CorrelationStats extends React.Component<IState, {}> {

    public render() {
        const {teams, onChangeFirstTeam, onChangeSecondTeam, firstTeamId, secondTeamId} = this.props;
        const teamsOptions = teams.map((team) => {
            return (<option key={team.teamId} value={team.teamId}
            >{team.name}</option>);
        });

        const teamSelect = (
            <div className={'row'}>
                <div className={'col-6'}>
                    <select className="form-control" onChange={(event) => {
                        onChangeFirstTeam(+event.target.value);
                    }}
                            value={this.props.firstTeamId}
                    >
                        <option value={null}>--select team--</option>
                        {teamsOptions}
                    </select>
                </div>
                <div className={'col-6'}>
                    <select className="form-control" onChange={(event) => {
                        onChangeSecondTeam(+event.target.value);
                    }} value={this.props.secondTeamId}
                    >
                        <option value={null}>--select team--</option>
                        {teamsOptions}
                    </select>
                </div>
            </div>
        );
        const firstSelectedTeam = teams.filter((team) => {
            return team.teamId === firstTeamId;
        })[0];

        const secondSelectedTeam = teams.filter((team) => {
            return team.teamId === secondTeamId;
        })[0];

        const headline = (
            <h2>{lang.getText('Correlation  ') +
            ((firstSelectedTeam && secondSelectedTeam) ? (firstSelectedTeam.name + ' VS ' + secondSelectedTeam.name) : '')}</h2>
        );

        return (
            <div>
                {headline}
                {teamSelect}
                {(firstTeamId && secondTeamId) ? <Table/> : <GlobalCorrelation/>}
            </div>
        );
    }
}

const mapStateToProps = (state: IFyziklaniStatisticsStore): IState => {
    return {
        firstTeamId: state.statistics.firstTeamId,
        secondTeamId: state.statistics.secondTeamId,
        teams: state.data.teams,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>):
    IState => {
    return {
        onChangeFirstTeam: (teamId) => dispatch(setFirstTeamId(+teamId)),
        onChangeSecondTeam: (teamId) => dispatch(setSecondTeamId(+teamId)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(CorrelationStats);

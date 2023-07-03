import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';
import { setNewState } from '../../actions/stats';
import PointsInTime from './line-chart';
import PieChart from './pie-chart';
import TimeLine from './timeline';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/LangContext';
import Legend from 'FKSDB/Components/Game/ResultsAndStatistics/Statistics/TeamStatistics/legend';

interface StateProps {
    teams: TeamModel[];
    teamId: number;
}

interface DispatchProps {
    onChangeFirstTeam(id: number): void;
}

class TeamStats extends React.Component<StateProps & DispatchProps, never> {
    static contextType = TranslatorContext;

    public render() {
        const translator = this.context;
        const {teams, onChangeFirstTeam, teamId} = this.props;
        const selectedTeam = teams.filter((team) => {
            return team.teamId === teamId;
        })[0];
        return <>
            <div className="panel color-auto">
                <div className="container">
                    <h2>
                        {translator.getText('Statistics of team ') + (selectedTeam ? selectedTeam.name : '')}
                    </h2>
                    <p>
                        <select className="form-control" onChange={(event) => {
                            onChangeFirstTeam(+event.target.value);
                        }}>
                            <option value={null}>--{translator.getText('select team')}--</option>
                            {teams.map((team) => {
                                return <option key={team.teamId} value={team.teamId}>{team.name}</option>;
                            })}
                        </select>
                    </p>
                </div>
            </div>
            {teamId && <>
                <div className="panel color-auto">
                    <div className="container">
                        <h2>{translator.getText('Success of submitting')}</h2>
                        <PieChart teamId={teamId}/>
                        <h3>Legend</h3>
                        <Legend/>
                    </div>
                </div>
                <div className="panel color-auto">
                    <div className="container">
                        <h2>{translator.getText('Time progress')}</h2>
                        <PointsInTime teamId={teamId}/>
                        <h3>Legend</h3>
                        <Legend/>
                    </div>
                </div>
                <div className="panel color-auto">
                    <div className="container">
                        <h2>{translator.getText('Timeline')}</h2>
                        <TimeLine teamId={teamId}/>
                        <h3>Legend</h3>
                        <Legend/>
                    </div>
                </div>
            </>}
        </>;
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        teamId: state.statistics.firstTeamId,
        teams: state.data.teams,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onChangeFirstTeam: (teamId) => dispatch(setNewState({firstTeamId: +teamId})),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(TeamStats);

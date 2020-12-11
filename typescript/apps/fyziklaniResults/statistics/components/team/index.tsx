import { ModelFyziklaniTeam } from '@FKSDB/Model/ORM/Models/Fyziklani/ModelFyziklaniTeam';
import { translator } from '@translator/Translator';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import ChartContainer from '../../../../../../app/Components/Controls/Chart/ChartContainer';
import { setFirstTeamId } from '../../actions';
import { Store as StatisticsStore } from '../../reducers';
import Legend from './legend';
import PointsInTime from './lineChart';
import PointsPie from './pieChart';
import TimeLine from './timeline';

interface StateProps {
    teams: ModelFyziklaniTeam[];
    teamId: number;
}

interface DispatchProps {
    onChangeFirstTeam(id: number): void;
}

class TeamStats extends React.Component<StateProps & DispatchProps, {}> {

    public render() {
        const {teams, onChangeFirstTeam, teamId} = this.props;

        const teamSelect = (
            <p>
                <select className="form-control" onChange={(event) => {
                    onChangeFirstTeam(+event.target.value);
                }}>
                    <option value={null}>--{translator.getText('select team')}--</option>
                    {teams.map((team) => {
                        return (<option key={team.teamId} value={team.teamId}>{team.name}</option>);
                    })}
                </select>
            </p>
        );
        const selectedTeam = teams.filter((team) => {
            return team.teamId === teamId;
        })[0];
//
        const headline = (
            <h2 className={'fyziklani-headline'}>
                {translator.getText('Statistic for team ') + (selectedTeam ? selectedTeam.name : '')}
            </h2>);
        return (<div>

            {teamSelect}
            {teamId && (<>
                {headline}
                <ChartContainer
                    chart={PointsPie}
                    chartProps={{teamId}}
                    legendComponent={Legend}
                    headline={translator.getText('Success of submitting')}
                />
                <hr/>
                <ChartContainer
                    chart={PointsInTime}
                    chartProps={{teamId}}
                    legendComponent={Legend}
                    headline={translator.getText('Time progress')}
                />
                <hr/>
                <ChartContainer
                    chart={TimeLine}
                    chartProps={{teamId}}
                    legendComponent={Legend}
                    headline={translator.getText('Timeline')}
                />
            </>)}
        </div>);
    }
}

const mapStateToProps = (state: StatisticsStore): StateProps => {
    return {
        teamId: state.statistics.firstTeamId,
        teams: state.data.teams,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onChangeFirstTeam: (teamId) => dispatch(setFirstTeamId(+teamId)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(TeamStats);

import * as React from 'react';
import { useContext } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { setNewState } from '../../actions/stats';
import PointsInTime from './line-chart';
import PieChart from './pie-chart';
import TimeLine from './timeline';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/context';
import Legend from 'FKSDB/Components/Game/ResultsAndStatistics/Statistics/TeamStatistics/legend';

export default function TeamStats() {
    const translator = useContext(TranslatorContext);
    const teamId = useSelector((state: Store) => state.statistics.firstTeamId);
    const teams = useSelector((state: Store) => state.data.teams);
    const dispatch = useDispatch();
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
                        dispatch(setNewState({firstTeamId: +event.target.value}))
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
                    <h3>{translator.getText('Legend')}</h3>
                    <Legend/>
                </div>
            </div>
            <div className="panel color-auto">
                <div className="container">
                    <h2>{translator.getText('Time progress')}</h2>
                    <PointsInTime teamId={teamId}/>
                    <h3>{translator.getText('Legend')}</h3>
                    <Legend/>
                </div>
            </div>
            <div className="panel color-auto">
                <div className="container">
                    <h2>{translator.getText('Timeline')}</h2>
                    <TimeLine teamId={teamId}/>
                    <h3>{translator.getText('Legend')}</h3>
                    <Legend/>
                </div>
            </div>
        </>}
    </>;
}

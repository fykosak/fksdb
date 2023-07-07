import * as React from 'react';
import { useContext } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { setNewState } from '../../actions/stats';
import GlobalCorrelation from './global-correlation';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/context';

export default function CorrelationStats() {
    const translator = useContext(TranslatorContext);
    const firstTeamId = useSelector((state: Store) => state.statistics.firstTeamId);
    const secondTeamId = useSelector((state: Store) => state.statistics.secondTeamId);
    const teams = useSelector((state: Store) => state.data.teams);
    const dispatch = useDispatch();
    const teamsOptions = teams.map((team) => {
        return <option key={team.teamId} value={team.teamId}
        >{team.name}</option>;
    });

    const teamSelect = (
        <div className="row">
            <div className="col-6">
                <select
                    className="form-control"
                    onChange={(event) => dispatch(setNewState({firstTeamId: +event.target.value}))}
                    value={firstTeamId}
                >
                    <option value={null}>--{translator.getText('select team')}--</option>
                    {teamsOptions}
                </select>
            </div>
            <div className="col-6">
                <select
                    className="form-control"
                    onChange={(event) =>
                        dispatch(setNewState({secondTeamId: +event.target.value}))}
                    value={secondTeamId}
                >
                    <option value={null}>--{translator.getText('select team')}--</option>
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
        <h2>{translator.getText('Correlation ') +
            ((firstSelectedTeam && secondSelectedTeam) ? (firstSelectedTeam.name + ' VS ' + secondSelectedTeam.name) : '')}</h2>
    );

    return <>
        {headline}
        {teamSelect}
        {(firstTeamId && secondTeamId) ? /*<Table/>*/null : <GlobalCorrelation/>}
    </>;
}

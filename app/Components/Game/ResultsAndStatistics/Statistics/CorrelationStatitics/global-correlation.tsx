import { scaleLinear } from 'd3-scale';
import * as React from 'react';
import { useContext } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { setNewState } from '../../actions/stats';
import { calculateCorrelation, getTimeLabel } from '../Middleware/correlation';
import { calculateSubmitsForTeams } from '../Middleware/submits-for-teams';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/context';

export default function GlobalCorrelation() {
    const translator = useContext(TranslatorContext);
    const color = scaleLinear<string, string>().domain([0, 1000 * 1000]).range(['#ff0000', '#ffffff']);
    const submits = useSelector((state: Store) => state.data.submits);
    const teams = useSelector((state: Store) => state.data.teams);
    const dispatch = useDispatch();

    const submitsForTeams = calculateSubmitsForTeams(submits);
    const rows = [];
    teams.forEach((firstTeam) => {
        teams.forEach((secondTeam) => {
            if (secondTeam.teamId <= firstTeam.teamId) {
                return;
            }
            const {avgNStdDev, countFiltered, countTotal} = calculateCorrelation(
                Object.hasOwn(submitsForTeams, firstTeam.teamId) ? submitsForTeams[firstTeam.teamId] : {},
                Object.hasOwn(submitsForTeams, secondTeam.teamId) ? submitsForTeams[secondTeam.teamId] : {},
            );
            rows.push(<tr key={secondTeam.teamId + '__' + firstTeam.teamId}>
                <td>{firstTeam.name}</td>
                <td>{secondTeam.name}</td>
                <td style={{backgroundColor: color(avgNStdDev.average)}}>
                    {getTimeLabel(avgNStdDev.average, avgNStdDev.standardDeviation)}
                </td>
                <td>{countFiltered}</td>
                <td>{countTotal}</td>
                <td>
                        <span className="btn btn-outline-primary btn-sm" onClick={() => {
                            dispatch(setNewState({secondTeamId: +secondTeam.teamId, firstTeamId: +firstTeam.teamId}));
                        }}>Detail</span>
                </td>
            </tr>);

        });
    });
    return <table className="table table-striped table-sm">
        <thead>
        <tr>
            <th>{translator.getText('First team')}</th>
            <th>{translator.getText('Second team')}</th>
            <th>{translator.getText('Average')}</th>
            <th>{translator.getText('Under 2 minutes')}</th>
            <th>{translator.getText('Both teams')}</th>
        </tr>
        </thead>
        <tbody>{rows}</tbody>
    </table>;
}

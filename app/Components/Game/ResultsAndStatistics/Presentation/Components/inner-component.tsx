import * as React from 'react';
import { useSelector } from 'react-redux';
import { calculate, Item } from '../../Helpers/calculate-data';
import Headline from './headline';
import TeamRow from './team-row';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

export default function InnerComponent() {

    const availablePoints = useSelector((state: Store) => state.data.availablePoints);
    const category = useSelector((state: Store) => state.presentation.category);
    const cols = useSelector((state: Store) => state.presentation.cols);
    const statePosition = useSelector((state: Store) => state.presentation.position);
    const rows = useSelector((state: Store) => state.presentation.rows);
    const submits = useSelector((state: Store) => state.data.submits);
    const teams = useSelector((state: Store) => state.data.teams);

    let position = statePosition;
    const submitsForTeams = calculate(submits, teams);

    const submitsForTeamsArray: Item<5 | 3 | 2 | 1>[] = [];
    for (const teamId in submitsForTeams) {
        if (Object.hasOwn(submitsForTeams, teamId)) {
            if (!category || (category === submitsForTeams[teamId].team.category)) {
                submitsForTeamsArray.push(submitsForTeams[teamId]);
            }
        }
    }
    submitsForTeamsArray.sort((a, b) => {
        return b.points - a.points;
    });

    const resultsItems = [];
    for (let col = 0; col < cols; col++) {
        const colItems = [];
        for (let row = 0; row < rows; row++) {
            if (Object.hasOwn(submitsForTeamsArray, position)) {
                const item = submitsForTeamsArray[position];
                position += 1;
                colItems.push(<TeamRow<5 | 3 | 2 | 1>
                    key={item.team.teamId}
                    item={item}
                    position={position}
                    availablePoints={availablePoints}
                />);
            }
        }

        let table = null;
        if (colItems.length) {
            table = <>
                <div className="row head-row">
                    <div className="col-1">Pos.</div>
                    <div className="col-1">Cat./Kat.</div>
                    <div className="col-4">Team/Tým</div>
                    <div className="col-1">∑</div>
                    <div className="col-1">N</div>
                    <div className="col-1">x̄</div>
                    <div className="col-3"/>
                </div>
                {colItems}
            </>;
        }

        switch (cols) {
            case 2:
                resultsItems.push(<div className="col-5" key={col}>{table}</div>);
                break;
            case 3:
                resultsItems.push(<div className="col-3" key={col}>{table}</div>);
                break;
            default:
            case 1:
                resultsItems.push(<div className="col-10" key={col}>{table}</div>);
        }
    }
    return <div className="p-3 h-100 bg-white">
        <Headline startPosition={statePosition + 1} endPosition={position} category={category}/>
        <div className="row justify-content-around">{resultsItems}</div>
    </div>;
}

import { Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniSubmit';
import { ModelFyziklaniTask } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTask';
import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';
import * as React from 'react';
import { connect } from 'react-redux';
import { calculate, Item } from '../../Helpers/calculateData';
import { FyziklaniPresentationStore } from '../Reducers';
import Headline from './Headline';
import TeamRow from './TeamRow';

interface StateProps {
    availablePoints: number[];
    category: string;
    submits: Submits;
    teams: ModelFyziklaniTeam[];
    tasks: ModelFyziklaniTask[];
    cols: number;
    rows: number;
    position: number;
}

class InnerComponent extends React.Component<StateProps> {

    public render() {
        const {submits, teams, rows, cols, category, position: statePosition, availablePoints} = this.props;
        let {position} = this.props;
        const submitsForTeams = calculate(submits, teams);

        const submitsForTeamsArray: Item[] = [];
        for (const teamId in submitsForTeams) {
            if (submitsForTeams.hasOwnProperty(teamId)) {
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
                if (submitsForTeamsArray.hasOwnProperty(position)) {
                    const item = submitsForTeamsArray[position];
                    position += 1;
                    colItems.push(<TeamRow key={item.team.teamId} item={item} position={position}
                                           availablePoints={availablePoints}/>);
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
        return (
            <div className="p-3 h-100 bg-white">
                <Headline startPosition={statePosition + 1} endPosition={position} category={category}/>
                <div className="row justify-content-around">{resultsItems}</div>
            </div>
        );
    }
}

const mapStateToProps = (state: FyziklaniPresentationStore): StateProps => {
    return {
        availablePoints: state.data.availablePoints,
        category: state.presentation.category,
        cols: state.presentation.cols,
        position: state.presentation.position,
        rows: state.presentation.rows,
        submits: state.data.submits,
        tasks: state.data.tasks,
        teams: state.data.teams,
    };
};

export default connect(mapStateToProps, null)(InnerComponent);

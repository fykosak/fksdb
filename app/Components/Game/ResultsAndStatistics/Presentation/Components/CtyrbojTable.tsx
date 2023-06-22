import { Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/SubmitModel';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';
import { connect } from 'react-redux';
import Row from './CtyrbojRow';
import { Store } from '../../reducers/store';
import { TranslatorContext } from '@translator/LangContext';

interface StateProps {
    submits: Submits;
    teams: TeamModel[];
    tasks: TaskModel[];
}

class Index extends React.Component<StateProps, never> {

    static contextType = TranslatorContext;

    public render() {
        const translator = this.context;
        const {submits, teams, tasks} = this.props;
        const submitsForTeams = {};
        for (const index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit = submits[index];
                const {teamId, taskId: taskId} = submit;
                submitsForTeams[teamId] = submitsForTeams[teamId] || {};
                submitsForTeams[teamId][taskId] = submit;
            }
        }

        return <div className="p-3 h-100 bg-white">
            <div className="row text-center mb-3">
                <h1>{translator.getText('Výsledkovky')}</h1>
            </div>
            <div className="container-fluid">
                <table className="table">
                    <thead>
                    <tr>
                        <th/>
                        <th>∑</th>
                        <th data-ctyrboj-label="A" className="text-center"><strong>BIOLOGIE</strong></th>
                        <th data-ctyrboj-label="B" className="text-center"><strong>CHEMIE</strong></th>
                        <th data-ctyrboj-label="C" className="text-center"><strong>FYZIKA</strong></th>
                        <th data-ctyrboj-label="D" className="text-center"><strong>MATEMATIKA</strong></th>
                    </tr>
                    </thead>
                    <tbody>
                    {teams.map((team: TeamModel, teamIndex) =>
                        <Row
                            tasks={tasks}
                            submits={submitsForTeams[team.teamId] || {}}
                            team={team}
                            key={teamIndex}
                        />)}
                    </tbody>
                </table>
            </div>
        </div>;
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        submits: state.data.submits,
        tasks: state.data.tasks,
        teams: state.data.teams,
    };
};

export default connect(mapStateToProps, null)(Index);

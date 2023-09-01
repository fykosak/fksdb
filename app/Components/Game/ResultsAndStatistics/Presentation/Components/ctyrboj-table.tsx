import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/team-model';
import * as React from 'react';
import { useContext } from 'react';
import { useSelector } from 'react-redux';
import Row from './ctyrboj-row';
import { Store } from '../../reducers/store';
import { TranslatorContext } from '@translator/context';

export default function Index() {

    const translator = useContext(TranslatorContext);
    const submits = useSelector((state: Store) => state.data.submits);
    const tasks = useSelector((state: Store) => state.data.tasks);
    const teams = useSelector((state: Store) => state.data.teams);

    const submitsForTeams = {};
    for (const index in submits) {
        if (Object.hasOwn(submits, index)) {
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

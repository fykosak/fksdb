import { SubmitModel, Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/SubmitModel';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';

export interface Item {
    team: TeamModel;
    submits: {
        [taskId: number]: SubmitModel;
    };
    points: number;
    groups: {
        1: number;
        2: number;
        3: number;
        5: number;
    };
    count: number;
}

export const calculate = (submits: Submits, teams: TeamModel[] = []): { [teamId: number]: Item } => {
    const submitsForTeams: {
        [teamId: number]: Item;
    } = {};
    teams.forEach((team) => {
        submitsForTeams[team.teamId] = {
            count: 0,
            groups: {1: 0, 2: 0, 3: 0, 5: 0},
            points: 0,
            submits: {},
            team,
        };
    });
    for (const index in submits) {
        if (submits.hasOwnProperty(index)) {
            const submit = submits[index];
            if (!submit.points) {
                continue;
            }

            const {teamId, taskId: taskId} = submit;
            const [selectedTeam] = teams.filter((team: TeamModel) => {
                return team.teamId === submit.teamId;
            });
            if (!selectedTeam) {
                console.log('team ' + submit.teamId + ' nexistuje');
                continue;
            }
            if (submitsForTeams.hasOwnProperty(teamId)) {
                submitsForTeams[teamId].submits[taskId] = submit;
                submitsForTeams[teamId].points += +submit.points;
                submitsForTeams[teamId].count++;
                submitsForTeams[teamId].groups[submit.points]++;
            }
        }
    }
    return submitsForTeams;
};

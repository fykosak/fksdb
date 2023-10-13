import { SubmitModel, Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/submit-model';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/team-model';

export interface Item<AvailablePoints extends number> {
    team: TeamModel;
    submits: {
        [taskId: number]: SubmitModel;
    };
    points: number;
    groups: {
        [points in AvailablePoints]: number;
    };
    count: number;
}

export const calculate = (submits: Submits, teams: TeamModel[] = []): { [teamId: number]: Item<5 | 3 | 2 | 1> } => {
    const submitsForTeams: {
        [teamId: number]: Item<5 | 3 | 2 | 1>;
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
        if (Object.hasOwn(submits, index)) {
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
            if (Object.hasOwn(submitsForTeams,teamId)) {
                submitsForTeams[teamId].submits[taskId] = submit;
                submitsForTeams[teamId].points += +submit.points;
                submitsForTeams[teamId].count++;
                submitsForTeams[teamId].groups[submit.points]++;
            }
        }
    }
    return submitsForTeams;
};

import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/team-model';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/task-model';

export default class TaskCodePreprocessor {
    private teams: TeamModel[];
    private tasks: TaskModel[];

    public constructor(teams: TeamModel[], tasks: TaskModel[]) {
        this.teams = teams;
        this.tasks = tasks;
    }

    public getTeam(code: string): TeamModel {
        if (code.length < 6) {
            throw new Error('Team id not completed');
        }
        const teamId = code.substring(0,6);
        const [filterTeams] = this.teams.filter((currentTeam) => {
            return currentTeam.teamId === +teamId;
        });
        if (!filterTeams) {
            throw new Error('Team does not exist');
        }
        return filterTeams;
    }

    public getTask(code: string): TaskModel {
        if (code.length < 8) {
            throw new Error('Task code not completed');
        }
        const taskLabel = code.substring(6, 8).toString();
        if (taskLabel === 'XX') {
            throw Error('No task left');
        }
        const [filterTask] = this.tasks.filter((currentTask) => {
            return currentTask.label === taskLabel;
        });
        if (!filterTask) {
            throw new Error('Task does not exist');
        }
        return filterTask;
    }

    public getSum(code: string): number {
        if (code.length < 9) {
            throw new Error('Code not complete');
        }
        if (code.length > 9) {
            throw new Error('Code is too long');
        }

        const subCode = code.split('').map((char): number => {
            return +char.toLocaleUpperCase()
                .replace('A', '1')
                .replace('B', '2')
                .replace('C', '3')
                .replace('D', '4')
                .replace('E', '5')
                .replace('F', '6')
                .replace('G', '7')
                .replace('H', '8');
        });

        const sum = (+subCode[0] + +subCode[3] + +subCode[6]) * 3 +
            (+subCode[1] + +subCode[4] + +subCode[7]) * 7 +
            (+subCode[2] + +subCode[5] + +subCode[8]);
        if (sum % 10 !== 0) {
            throw new Error('Invalid control');
        }
        return sum;
    }
}


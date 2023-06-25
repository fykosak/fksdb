import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';

export default class TaskCodePreprocessor {
    private teams: TeamModel[];
    private tasks: TaskModel[];

    public constructor(teams: TeamModel[], tasks: TaskModel[]) {
        this.teams = teams;
        this.tasks = tasks;
    }

    public getTeam(code: string): TeamModel {
        const teamId = this.extractTeamId(code);
        const filterTeams = this.teams.filter((currentTeam) => {
            return currentTeam.teamId === +teamId;
        });
        if (!filterTeams.length) {
            throw new Error('Team does not exists.');
        }
        return filterTeams[0];
    }

    public getTask(code: string): TaskModel {
        const taskLabel = this.extractTaskLabel(code);
        if (taskLabel === 'XX') {
            throw Error('No task left');
        }
        const filterTask = this.tasks.filter((currentTask) => {
            return currentTask.label === taskLabel;
        });
        if (!filterTask.length) {
            throw new Error('Task does not exists.');
        }
        return filterTask[0];
    }

    private extractTeamId(code: string): number {
        const fullCode = this.createFullCode(code);
        return +fullCode.substring(0, 6);
    }

    private extractTaskLabel(code: string): string {
        const fullCode = this.createFullCode(code);
        return fullCode.substring(6, 8).toString();
    }

    private createFullCode(code: string): string {
        if (code.length > 9) {
            throw new Error('Code is too long');
        }

        const fullCode = (('0').repeat(9 - code.length) + code).toLocaleUpperCase();

        const subCode = fullCode.split('').map((char): number => {
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
        return fullCode;
    }
}


import { OwnProps } from './Components/MainForm';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import { FormErrors } from 'redux-form';

export const validate = <FormData extends { code?: string }>(values: { code?: string }, props: OwnProps): FormErrors<FormData> => {
    const errors: FormErrors<FormData> = {};

    if (!values.code) {
        errors.code = 'Code is empty.';
        return errors;
    }
    try {
        getTeam(values.code, props.teams);
        getTask(values.code, props.tasks);
    } catch (e) {
        errors.code = e.message;
    }
    return errors;
};

export const getTeam = (code: string, teams: TeamModel[]): TeamModel => {
    const teamId = extractTeamId(code);
    const filterTeams = teams.filter((currentTeam) => {
        return currentTeam.teamId === +teamId;
    });
    if (!filterTeams.length) {
        throw new Error('Team does not exists.');
    }
    return filterTeams[0];
}

export const getTask = (code: string, tasks: TaskModel[]): TaskModel => {
    const taskLabel = extractTaskLabel(code);
    console.log(taskLabel);
    const filterTask = tasks.filter((currentTask) => {
        return currentTask.label === taskLabel;
    });
    if (!filterTask.length) {
        throw new Error('Task does not exists.');
    }
    return filterTask[0];
}

const extractTeamId = (code: string): number => {
    const fullCode = createFullCode(code);
    return +fullCode.substring(0, 6);
}

const extractTaskLabel = (code: string): string => {
    const fullCode = createFullCode(code);
    return fullCode.substring(6, 8).toString();
}

const createFullCode = (code: string): string => {
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

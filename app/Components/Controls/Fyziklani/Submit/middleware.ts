import { OwnProps } from 'FKSDB/Components/Controls/Fyziklani/Submit/Components/Container';
import { ModelFyziklaniTask } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTask';
import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';
import { FormErrors } from 'redux-form';

export const getFullCode = (code: string): string => {
    const length = code.length;
    return (('0').repeat(9 - length) + code).toLocaleUpperCase();
};

const isValidFullCode = (code: string): boolean => {

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
    return (getControl(subCode) % 10 === 0);
};

const getControl = (subCode: Array<string | number>): number => {
    return (+subCode[0] + +subCode[3] + +subCode[6]) * 3 +
        (+subCode[1] + +subCode[4] + +subCode[7]) * 7 +
        (+subCode[2] + +subCode[5] + +subCode[8]);
};
export const getTeam = (fullCode: string, teams: ModelFyziklaniTeam[]): ModelFyziklaniTeam => {
    const matchedTeam = fullCode.match(/^([0-9]+)/);
    if (!matchedTeam) {
        return null;
    }
    return teams.filter((currentTeam) => {
        return currentTeam.teamId === +matchedTeam[1];
    })[0];
};

export const getTask = (fullCode: string, tasks: ModelFyziklaniTask[]): ModelFyziklaniTask => {
    const matchedLabel = fullCode.match(/^[0-9]+([a-zA-Z]{2})/);

    if (!matchedLabel) {
        return null;
    }
    return tasks.filter((currentTask) => {
        return currentTask.label === matchedLabel[1].toUpperCase();
    })[0];
};

export const validate = (values, props: OwnProps): FormErrors<any> => {
    const errors: { code?: string } = {};

    if (!values.code) {
        errors.code = 'Code is empty.';
        return errors;
    }
    const fullCode = getFullCode(values.code);

    if (!getTeam(fullCode, props.teams)) {
        errors.code = 'Team does not exists.';
    }
    if (!getTask(fullCode, props.tasks)) {
        errors.code = 'Task does not exists.';
    }

    if (fullCode.match(/[a-zA-Z]{2}([0-9])$/)) {
        if (!isValidFullCode(fullCode)) {
            errors.code = 'Invalid control';
        }
    }
    if (!fullCode.match(/^([0-9]{6}[a-zA-Z]{2}[0-9])$/)) {
        errors.code = 'Invalid code format';
    }

    return errors;
};

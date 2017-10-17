import { IProps } from '../components/inputs-container';

export const getFullCode = (values): string => {
    const teamString = (+values.team < 1000) ? '0' + +values.team : +values.team;
    return '00' + teamString + values.task + values.control;
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

export const validate = (values, props: IProps) => {
    const errors: any = {};
    if (values.team) {
        const teams = props.teams.filter((currentTeam) => {
            return currentTeam.team_id === +values.team;
        });
        if (!teams.length) {
            errors.team = { type: 'danger', msg: 'Team does not exist' };
        }
    }
    if (values.task) {
        const tasks = props.tasks.filter((currentTask) => {
            return currentTask.label === values.task.toUpperCase();
        });
        if (!tasks.length) {
            errors.task = { type: 'danger', msg: 'Task does not exist' };
        }
    }
    if ((!errors.task && !errors.team && values.control)) {
        const code = getFullCode(values);
        if (!isValidFullCode(code)) {
            errors.control = { type: 'danger', msg: 'Invalid control' };
        }
    }
    if (!values.hasOwnProperty('control') || values.control === '') {
        errors.control = { type: 'danger', msg: 'Required' };
    }
    if (!values.hasOwnProperty('team') || !values.team) {
        errors.team = { type: 'danger', msg: 'Required' };
    }
    if (!values.hasOwnProperty('task') || !values.task) {
        errors.task = { type: 'danger', msg: 'Required' };
    }
    return errors;
};

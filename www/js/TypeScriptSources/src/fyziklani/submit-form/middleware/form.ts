import { IProps } from '../components/form-section';

export const getFullCode = (values): string => {
    const length = values.code.length;
    return (('0').repeat(9 - length) + values.code).toLocaleUpperCase();
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
    const errors: { code?: any } = {};

    if (!values.code) {
        errors.code = { type: 'danger', msg: 'Code is empty' };
        return errors;
    }
    const length = values.code.length;
    const code = '0'.repeat(9 - length) + values.code;

    const matchedTeam = code.match(/^([0-9]+)/);

    if (!props.teams.some((currentTeam) => {
            return currentTeam.teamId === +matchedTeam[1];
        })) {
        errors.code = { type: 'danger', msg: 'Team does not exist' };
    }

    const matchedLabel = code.match(/([a-zA-Z]{2})/);
    if (matchedLabel) {
        // const label = extractTaskLabel(code);
        if (!props.tasks.some((currentTask) => {
                return currentTask.label === matchedLabel[1].toUpperCase();
            })) {
            errors.code = { type: 'danger', msg: 'Task does not exist' };
        }
    }
    const matchedControl = code.match(/[a-zA-Z]{2}([0-9])/);
    if (matchedControl) {
        if (!isValidFullCode(code)) {
            errors.code = { type: 'danger', msg: 'Invalid control' };
        }
    }
    if (!code.match(/([0-9]{6}[a-zA-Z]{2}[0-9])/)) {
        errors.code = { type: 'danger', msg: 'Code is too sort' };
    }

    return errors;
};

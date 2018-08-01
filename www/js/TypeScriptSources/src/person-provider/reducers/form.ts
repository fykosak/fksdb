import { ACTION_CHANGE_EMAIL_VALUE } from '../actions';
import {
    isMail,
    required,
} from '../validation';

export interface IFormState {
    type: string;
    email?: {
        valid: boolean;
        value: string;
    };
    select?: {
        options: any[];
        payload: string;
        value: number;
    };
}

const setEmailValue = (state: IFormState, action): IFormState => {
    const {value} = action;
    return {
        ...state,
        email: {
            valid: isMail(value) && required(value),
            value,
        },
    };
};

export const form = (state: IFormState = {type: null}, event): IFormState => {
    switch (event.type) {
        case ACTION_CHANGE_EMAIL_VALUE:
            return setEmailValue(state, event);
        default:
            return state;
    }
};

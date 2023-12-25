import { ACTION_ADD_ERROR, ActionAddError } from 'FKSDB/Components/Controls/Upload/AjaxSubmit/actions';
import { ACTION_FETCH_START } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/actions';
import { Message } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';

export type State = Message[];

const addError = (state: Message[], action: ActionAddError): Message[] => {
    return [...state, action.error];
};
const clearErrors = (): Message[] => {
    return [];
};

export const errors = (state: Message[] = [], action): Message[] => {
    switch (action.type) {
        case ACTION_ADD_ERROR:
            return addError(state, <ActionAddError>action);
        case ACTION_FETCH_START:
            return clearErrors();
        default:
            return state;
    }
};

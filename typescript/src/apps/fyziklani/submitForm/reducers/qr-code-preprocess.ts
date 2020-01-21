import { Action } from 'redux';

export interface QrCodePreprocessState {
    processing: boolean;
}

const setProcessing = (state: QrCodePreprocessState, value: boolean): QrCodePreprocessState => {
    return {
        ...state,
        processing: value,
    };
};

/*
export function qrCodePreprocess<A extends Action<string>>(state: QrCodePreprocessState = {processing: false}, action: A): QrCodePreprocessState {
    switch (action.type) {
        case ACTION_QR_CODE_PROSSING_START:
            return setProcessing(state, true);
        case ACTION_QR_CODE_PROSSING_END:
            return setProcessing(state, false);
        default:
            return state;
    }
}*/

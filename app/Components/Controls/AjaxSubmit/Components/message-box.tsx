import { FetchStateMap } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/reducer';
import * as React from 'react';
import { useSelector } from 'react-redux';
import { State as ErrorLoggerState } from '../Reducers/errors';

export default function MessageBox() {
    const messages = useSelector((state: Store) => [
        ...state.fetch.messages,
        ...state.errorLogger.errors,
    ]);
    return <>
        {messages.map((message, index) =>
            <div key={index} className={'alert alert-' + message.level}> {message.text}</div>)}
    </>;
}

interface Store {
    fetch: FetchStateMap;
    errorLogger: ErrorLoggerState;
}

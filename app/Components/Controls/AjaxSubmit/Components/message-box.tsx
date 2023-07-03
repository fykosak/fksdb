import { Message } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import { FetchStateMap } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/reducer';
import * as React from 'react';
import { connect } from 'react-redux';
import { State as ErrorLoggerState } from '../Reducers/errors';

interface StateProps {
    messages: Message[];
}

class MessageBox extends React.Component<StateProps, never> {
    public render() {
        const {messages} = this.props;
        return <>
            {messages.map((message, index) =>
                <div key={index} className={'alert alert-' + message.level}> {message.text}</div>)}
        </>;
    }
}

interface Store {
    fetch: FetchStateMap;
    errorLogger: ErrorLoggerState;
}

const mapStateToProps = (state: Store): StateProps => {
    const messages = state.fetch.messages;
    return {
        messages: [
            ...messages,
            ...state.errorLogger.errors,
        ],
    };
};

export default connect(mapStateToProps, null)(MessageBox);

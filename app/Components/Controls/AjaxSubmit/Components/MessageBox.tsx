import { Message } from 'FKSDB/Models/FrontEnd/Fetch/interfaces';
import { FetchApiState } from 'FKSDB/Models/FrontEnd/Fetch/reducer';
import * as React from 'react';
import { connect } from 'react-redux';
import { State as ErrorLoggerState } from '../Reducers/errorLogger';

interface StateProps {
    messages: Message[];
}

class MessageBox extends React.Component<StateProps, {}> {
    public render() {
        const {messages} = this.props;
        return <>{messages.map((message, index) => {
            return (<div key={index} className={'react-message alert alert-' + message.level}> {message.text}</div>);
        })}</>;
    }
}

interface Store {
    fetchApi: FetchApiState;
    errorLogger: ErrorLoggerState;
}

const mapStateToProps = (state: Store): StateProps => {
    const messages = state.fetchApi.messages;
    return {
        messages: [
            ...messages,
            ...state.errorLogger.errors,
        ],
    };
};

export default connect(mapStateToProps, null)(MessageBox);

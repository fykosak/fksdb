import * as React from 'react';
import { connect } from 'react-redux';
import { Message } from '../../fetch-api/middleware/interfaces';
import { State as SubmitState } from '../../fetch-api/reducers/submit';
import { State as ErrorLoggerState } from '../reducers/errorLogger';

interface State {
    messages?: Message[];
}

interface Props {
    accessKey: string;
}

class MessageBox extends React.Component<State & Props, {}> {
    public render() {
        const {messages} = this.props;
        return <>{messages.map((message, index) => {
            return (<div key={index} className={'react-message alert alert-' + message.level}> {message.text}</div>);
        })}</>;
    }
}

interface Store {
    fetchApi: SubmitState;
    errorLogger: ErrorLoggerState;
}

const mapStateToProps = (state: Store, ownProps: Props): State => {
    const messages = state.fetchApi.hasOwnProperty(ownProps.accessKey) ? state.fetchApi[ownProps.accessKey].messages : [];
    return {
        messages: [
            ...messages,
            ...state.errorLogger.errors],
    };
};
const mapDispatchToProps = (): State => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(MessageBox);

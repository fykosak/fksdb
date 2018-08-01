import * as React from 'react';
import { connect } from 'react-redux';
import FormDisplay from './form-display/';
import Form from './form/';
import { IStore } from '../reducers';
import { setHtml } from '../actions/external-form';

interface IProps {
    html: string;
    hasErrors: boolean;
    legend: string;
}

interface IState {
    isServed?: boolean;
    handleSetHtml?: (html) => void;
    hasHtml?: boolean;
}

class PersonProvider extends React.Component<IProps & IState, {}> {
    public componentDidMount() {
        this.props.handleSetHtml(this.props.html);
    }

    public render() {
        const {hasErrors, hasHtml} = this.props;

        if (hasHtml) {
            return <FormDisplay hasErrors={hasErrors} legend={this.props.legend}/>;
        } else {
            return <Form accessKey={'a'}/>;
        }
    }
}

const mapDispatchToProps = (dispatch): IState => {
    return {
        handleSetHtml: (html) => dispatch(setHtml(html)),
    };
};

const mapStateToProps = (state: IStore): IState => {
    return {
        hasHtml: !!state.externalForm.html,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(PersonProvider);

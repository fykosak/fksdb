import * as React from 'react';
import { connect } from 'react-redux';
import { WrappedFieldProps } from 'redux-form';
import {
    IProviderValue,
    IStore,
} from '../../interfaces';

interface IState {
    providerProperty?: IProviderValue<any>;
}

interface IProps {
    accessKey: string;
}

class Input extends React.Component<WrappedFieldProps & IProps & IState, {}> {

    public componentDidMount() {
        if (!this.props.providerProperty) {
            return;
        }
        const {providerProperty: {hasValue, value}, input: {onChange}} = this.props;
        if (hasValue) {
            onChange(value);
        }
    }

    public render() {
        return null;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IStore, ownProps: WrappedFieldProps & IProps): IState => {

    const {accessKey, input: {name}} = ownProps;
    if (state.provider.hasOwnProperty(accessKey)) {
        return {
            providerProperty: state.provider[accessKey].fields[name],
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(Input);

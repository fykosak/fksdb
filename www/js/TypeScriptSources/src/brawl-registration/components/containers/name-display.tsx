import { connect } from 'react-redux';
import * as React from 'react';
import { getFieldName } from './persons';

interface IProps {
    type: string;
    index: number;
}

interface IState {
    familyName?: { hasValue: boolean; value: string };
    otherName?: { hasValue: boolean; value: string };
}

class NameDisplay extends React.Component<IProps & IState, {}> {
    public render() {
        const {index, type, familyName, otherName} = this.props;
        return <>
            {(otherName && familyName) ?
                (<span>{otherName.value} {familyName.value}</span>) :
                (<span>{index + 1} {type}</span>)}
        </>;

    }
}

const mapDispatchToProps = () => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    const accessKey = getFieldName(ownProps.type, ownProps.index);
    if (state.provider.hasOwnProperty(accessKey)) {
        return {
            familyName: state.provider[accessKey].familyName,
            otherName: state.provider[accessKey].otherName,
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(NameDisplay);

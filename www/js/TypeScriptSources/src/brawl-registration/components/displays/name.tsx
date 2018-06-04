import * as React from 'react';
import { connect } from 'react-redux';
import Lang from '../../../lang/components/lang';
import {
    getParticipantValues,
    IPersonSelector,
} from '../../middleware/price';
import { FORM_NAME } from '../form';

interface IState {
    familyName?: string;
    otherName?: string;
}

class NameDisplay extends React.Component<IPersonSelector & IState, {}> {
    public render() {
        const {index, type, familyName, otherName} = this.props;
        return <>
            {(otherName && familyName) ?
                (<span>{otherName} {familyName}</span>) :
                (<span>{index + 1} <Lang text={type}/></span>)}
        </>;
    }
}

const mapDispatchToProps = () => {
    return {};
};

const mapStateToProps = (state, ownProps: IPersonSelector): IState => {
    const values = getParticipantValues(FORM_NAME, state, ownProps);
    if (!values.personInfo) {
        return {};
    }
    return {
        familyName: values.personInfo.familyName,
        otherName: values.personInfo.otherName,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(NameDisplay);

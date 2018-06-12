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

interface IProps {
    invalid?: boolean;
}

class NameDisplay extends React.Component<IPersonSelector & IState & IProps, {}> {
    public render() {
        const {index, type, familyName, otherName, invalid} = this.props;
        const hasName = !!(otherName && familyName);
        return <span className={(invalid ? 'text-danger' : (hasName ? 'text-success' : 'text-muted'))}>
            <span className={(type === 'teacher') ? 'fa fa-graduation-cap mr-2' : 'fa fa-user mr-2'}/>
            {(hasName) ?
                (<span>{otherName} {familyName}</span>) :
                (<span>{index + 1} <Lang text={type}/></span>)}
        </span>;
    }
}

const mapDispatchToProps = () => {
    return {};
};

const mapStateToProps = (state, ownProps: IPersonSelector): IState => {
    const values = getParticipantValues(FORM_NAME, state, ownProps);
    if (!values.person) {
        return {};
    }
    return {
        familyName: values.person.familyName,
        otherName: values.person.otherName,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(NameDisplay);

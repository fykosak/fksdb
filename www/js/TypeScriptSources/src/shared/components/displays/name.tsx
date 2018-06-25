import * as React from 'react';
import { connect } from 'react-redux';
import { FORM_NAME } from '../../../brawl-registration/components/form';
import {
    getParticipantValues,
    IPersonSelector,
} from '../../../brawl-registration/middleware/price';
import Lang from '../../../lang/components/lang';

interface IState {
    familyName?: string;
    otherName?: string;
}

interface IProps {
    invalid?: boolean;
    personSelector: IPersonSelector;
}

class NameDisplay extends React.Component<IState & IProps, {}> {
    public render() {
        const {personSelector: {index, type}, familyName, otherName, invalid} = this.props;
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

const mapStateToProps = (state, ownProps: IProps): IState => {
    const values = getParticipantValues(FORM_NAME, state, ownProps.personSelector);
    if (!values.person) {
        return {};
    }
    return {
        familyName: values.person.familyName,
        otherName: values.person.otherName,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(NameDisplay);

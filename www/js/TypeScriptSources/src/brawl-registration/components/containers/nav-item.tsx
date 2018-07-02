import * as React from 'react';
import { connect } from 'react-redux';
import {
    getFormAsyncErrors,
    getFormSyncErrors,
} from 'redux-form';
import Nav from '../../../shared/components/tabs/nav';
import { IPersonSelector } from '../../middleware/price';
import NameDisplay from '../../../shared/components/displays/name';
import { FORM_NAME } from '../form';

interface IProps {
    personSelector: IPersonSelector;
    active: boolean;
}

interface IState {
    syncErrors?: {
        [key: string]: string;
    };
    asyncErrors?: {
        [key: string]: string;
    };
}

class NavItem extends React.Component<IProps & IState, {}> {
    public render() {
        const {personSelector, active, syncErrors, asyncErrors} = this.props;
        const invalid = !!(syncErrors || asyncErrors);
        return <Nav active={active} name={personSelector.accessKey}>
            <NameDisplay personSelector={personSelector} invalid={invalid}/>
        </Nav>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    const allSyncErrors = getFormSyncErrors(FORM_NAME)(state);
    const allAsyncErrors = getFormAsyncErrors(FORM_NAME)(state);
    const data = {
        asyncErrors: undefined,
        syncErrors: undefined,
    };
    if (allSyncErrors && allSyncErrors.hasOwnProperty(ownProps.personSelector.type)) {
        if (allSyncErrors[ownProps.personSelector.type].hasOwnProperty(ownProps.personSelector.index)) {
            data.syncErrors = allSyncErrors[ownProps.personSelector.type][ownProps.personSelector.index];
        }
    }
    if (allAsyncErrors && allAsyncErrors.hasOwnProperty(ownProps.personSelector.type)) {
        if (allAsyncErrors[ownProps.personSelector.type].hasOwnProperty(ownProps.personSelector.index)) {
            data.asyncErrors = allAsyncErrors[ownProps.personSelector.type][ownProps.personSelector.index];
        }
    }
    return data;
};

export default connect(mapStateToProps, mapDispatchToProps)(NavItem);

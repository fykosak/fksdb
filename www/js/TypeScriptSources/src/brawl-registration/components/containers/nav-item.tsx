import * as React from 'react';
import { connect } from 'react-redux';
import {
    getFormAsyncErrors,
    getFormSyncErrors,
} from 'redux-form';
import NameDisplay from '../displays/name';
import { FORM_NAME } from '../form';
import Nav from '../helpers/tabs/nav';

interface IProps {
    type: string;
    index: number;
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
        const {index, type, active, syncErrors, asyncErrors} = this.props;
        const invalid = (syncErrors || asyncErrors);
        return <Nav active={active} name={(type + index)}>
            <NameDisplay type={type} index={index} invalid={invalid}/>
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
    if (allSyncErrors && allSyncErrors.hasOwnProperty(ownProps.type)) {
        if (allSyncErrors[ownProps.type].hasOwnProperty(ownProps.index)) {
            data.syncErrors = allSyncErrors[ownProps.type][ownProps.index];
        }
    }
    if (allAsyncErrors && allAsyncErrors.hasOwnProperty(ownProps.type)) {
        if (allAsyncErrors[ownProps.type].hasOwnProperty(ownProps.index)) {
            data.asyncErrors = allAsyncErrors[ownProps.type][ownProps.index];
        }
    }
    return data;
};

export default connect(mapStateToProps, mapDispatchToProps)(NavItem);

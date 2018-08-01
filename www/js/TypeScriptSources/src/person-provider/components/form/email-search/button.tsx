import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { findButtonClick } from '../../../actions';
import { IStore } from '../../../reducers';

// import Lang from '../../../lang/components/lang';
interface IProps {
    accessKey: string;
}

interface IState {
    valid?: boolean;
    submitting?: boolean;
    value?: string;
    onFindButtonClick?: (value: string) => Promise<any>;
}

class Field extends React.Component<IState & IProps, {}> {
    public render() {
        const {submitting, onFindButtonClick, valid, value} = this.props;

        return <button className="btn btn-primary" disabled={submitting || !valid} onClick={(event) => {
            event.preventDefault();
            return onFindButtonClick(value);
        }}>Search <span className={'fa fa-search'}/>
        </button>;
    }
}

// <Lang text={'search'}/>

const mapDispatchToProps = (dispatch: Dispatch<IStore>, ownProps: IProps): IState => {
    const {accessKey} = ownProps;
    return {
        onFindButtonClick: (value) => findButtonClick(dispatch, value, accessKey),
    };
};
const mapStateToProps = (state: IStore, ownProps: IProps): IState => {
    const {accessKey} = ownProps;
    return {
        submitting: state.submit.hasOwnProperty(accessKey) ? state.submit[accessKey].submitting : false,
        valid: state.form.hasOwnProperty('email') ? state.form.email.valid : false,
        value: state.form.hasOwnProperty('email') ? state.form.email.value : null,
    };

};

export default connect(mapStateToProps, mapDispatchToProps)(Field);

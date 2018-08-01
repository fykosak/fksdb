import * as React from 'react';
import { connect } from 'react-redux';

import {
    clearHtml,
    toggleForm,
} from '../../actions/external-form';
import { IStore } from '../../reducers';

interface IState {
    html?: string;
    clearForm?: () => void;
    toggleForm?: () => void;
}

interface IProps {
    hasErrors: boolean;
    legend: string;
}

class PersonProvider extends React.Component<IState & IProps, {}> {

    public render() {
        const {html, hasErrors, clearForm} = this.props;

        return <div>
            <button className={'btn btn-danger'} onClick={(event) => {
                event.preventDefault();
                clearForm();
            }}><span className={'fa fa-times mr-1'}/>Zrušiť osobu
            </button>
            <div dangerouslySetInnerHTML={{__html: html}}/>
        </div>;
    }
}

const mapDispatchToProps = (dispatch): IState => {
    return {
        clearForm: () => dispatch(clearHtml()),
        toggleForm: () => dispatch(toggleForm()),
    };
};

const mapStateToProps = (state: IStore): IState => {
    return {
        html: state.externalForm.html,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(PersonProvider);

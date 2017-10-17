import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { IStore } from '../reducers/index';
import { submitStart } from '../actions/index';

interface IProps {
}

interface IState {
    valid?: boolean;
    onSubmit?: (points: number) => void;
    submitting?: boolean;
}

class TaskInput extends React.Component<IProps & IState, {}> {

    public render() {
        const { valid, onSubmit, submitting } = this.props;

        const buttons = [5, 3, 2, 1].map((value, index) => {
            return (<button
                className="btn btn-lg btn-block btn-primary"
                type="button"
                key={index}
                disabled={!valid || submitting}
                onClick={(event) => valid ? onSubmit(value) : null}
            >{submitting ? (<i className="fa fa-spinner" aria-hidden="true"/>) : (value + '. bodu')}</button>);
        });
        return (
            <div className="row">
                {buttons}
            </div>
        );
    }
}
const mapStateToProps = (state: IStore): IState => {
    return {
        submitting: state.submit.submitting,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onSubmit: (points: number) => dispatch(submitStart(dispatch, points)),
    };
};
export default connect(mapStateToProps, mapDispatchToProps)(TaskInput);

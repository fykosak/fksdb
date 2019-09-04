import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { setDelay } from '../../../../actions/presentation/setDelay';
import { FyziklaniResultsStore } from '../../../../reducers';

interface StateProps {
    delay: number;
}

interface DispatchProps {
    onSetDelay(position: number): void;
}

class DelayField extends React.Component<StateProps & DispatchProps, {}> {

    public render() {
        const {delay, onSetDelay} = this.props;
        return <div className="form-group">
            <div className={'form-group'}>
                <label>Delay</label>
                <input name={'delay'} className={'form-control'} value={delay} type={'number'} max={60 * 1000} min={1000}
                       step={1000}
                       onChange={(e) => {
                           onSetDelay(+e.target.value);
                       }}/>
            </div>
        </div>;
    }
}

const mapStateToPros = (state: FyziklaniResultsStore): StateProps => {
    return {
        delay: state.presentation.delay,
    };
};
const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onSetDelay: (position: number) => dispatch(setDelay(position)),
    };
};

export default connect(mapStateToPros, mapDispatchToProps)(DelayField);

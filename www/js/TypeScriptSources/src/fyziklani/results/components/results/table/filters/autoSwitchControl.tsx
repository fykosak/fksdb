import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { lang } from '../../../../../../i18n/i18n';
import { setAutoSwitch } from '../../../../actions/table-filter';
import { FyziklaniResultsStore } from '../../../../reducers';

interface State {
    autoSwitch?: boolean;

    onAutoSwitch?(state: boolean): void;
}

class AutoSwitchControl extends React.Component<State, {}> {

    public render() {
        const {autoSwitch, onAutoSwitch} = this.props;

        return <>
            <h5>{lang.getText('Auto switch')}</h5>
            <button
                className={'btn ' + (autoSwitch ? 'btn-danger' : 'btn-success')}
                onClick={() => {
                    onAutoSwitch(!autoSwitch);
                }}
            >{autoSwitch ?
                (<><i className="fa fa-pause mr-3"/>{lang.getText('stop auto switch')}</>) :
                (<><i className="fa fa-play mr-3"/>{lang.getText('run auto switch')}</>)
            }</button>

        </>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action>): State => {
    return {
        onAutoSwitch: (state) => dispatch(setAutoSwitch(state)),
    };
};
const mapStateToPros = (state: FyziklaniResultsStore): State => {
    return {
        autoSwitch: state.tableFilter.autoSwitch,
    };
};

export default connect(
    mapStateToPros,
    mapDispatchToProps,
)(AutoSwitchControl);

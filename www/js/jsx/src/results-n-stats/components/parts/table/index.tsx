import * as React from 'react';

import AutoFilter from '../../helpers/auto-filter';
import Timer from '../timer';
import Images from './images';
import Options from './options';
import ResultsTable from './results-table';

import { connect } from 'react-redux';
import { IStore } from '../../../reducers/index';

interface IState {
    visible?: boolean;
    hardVisible?: boolean;
}

class Results extends React.Component<IState, {}> {

    public render() {
        const { visible, hardVisible } = this.props;

        const msg = [];
        // TODO do samostatného componentu všetky messages
        if (hardVisible) {
            msg.push(<div key={msg.length} className="alert alert-warning">
                Výsledková listina je určená pouze pro organizátory!!!</div>);
        }

        return (
            <div>
                {msg}
                <AutoFilter/>
                {(visible || hardVisible) ? (<ResultsTable/>) : (<Images/>)}
                <Timer/>
                <Options/>
            </div >
        );
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        hardVisible: state.options.hardVisible,
        visible: state.timer.visible,
    };
};

export default connect(mapStateToProps, null)(Results);

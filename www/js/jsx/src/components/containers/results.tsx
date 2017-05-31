import * as React from 'react';
import {connect} from 'react-redux';

import Images from '../parts/images';
import ResultsTable from '../table/results-table';
import Timer from '../parts/timer';
import AutoFilter from '../parts/auto-filter';
import Options from '../parts/options';

interface IProps {
    visible?: boolean;
    hardVisible?: boolean;
}

class Results extends React.Component<IProps, any> {

    public render() {
        const {visible, hardVisible} = this.props;

        const msg = [];
        // TODO do samostatného componentu všetky messages
        if (hardVisible) {
            msg.push(<div key={msg.length} className="alert alert-warning">
                Výsledková listina je určená pouze pro organizátory!!!</div> );
        }

        return (<div>
                {msg}
                <AutoFilter/>
                {!(visible || hardVisible) && (<Images/>)}
                {(visible || hardVisible) && ( <ResultsTable/>)}
                <Timer/>
                <Options/>
            </div >
        );
    }
}

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        hardVisible: state.options.hardVisible,
        visible: state.timer.visible,
    }
};

export default connect(mapStateToProps, null)(Results);

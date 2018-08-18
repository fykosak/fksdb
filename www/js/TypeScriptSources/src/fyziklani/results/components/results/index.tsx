import * as React from 'react';
import { connect } from 'react-redux';
import Timer from '../../../helpers/components/timer';
import { IFyziklaniResultsStore } from '../../reducers';
import AutoFilter from './filter/index';
import Images from './images';
import ResultsTable from './results-table';

interface IState {
    visible?: boolean;
    hardVisible?: boolean;
}

interface IProps {
    basePath: string;
    accessKey: string;
    mode: string;
}

class Results extends React.Component<IState & IProps, {}> {

    public render() {
        const {visible, hardVisible, basePath, mode} = this.props;

        const msg = [];
        // TODO do samostatného componentu všetky messages
        if (hardVisible) {
            msg.push(<div key={msg.length} className="alert alert-warning">
                Výsledková listina je určená pouze pro organizátory!!!</div>);
        }

        return (
            <div>
                {msg}

                {(mode === 'presentation') && <><Timer/><AutoFilter/></>}
                {(visible || hardVisible) ? (<ResultsTable/>) : (<Images basePath={basePath}/>)}
            </div>
        );
    }
}

const mapStateToProps = (state: IFyziklaniResultsStore): IState => {
    return {
        hardVisible: state.options.hardVisible,
        visible: state.timer.visible,
    };
};

export default connect(mapStateToProps, (): IState => {
    return {};
})(Results);

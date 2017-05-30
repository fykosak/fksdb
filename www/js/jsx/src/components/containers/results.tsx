import * as React from 'react';
import {connect} from 'react-redux';

import Images from '../parts/images';
import ResultsTable from '../table/results-table';
import Timer from '../parts/timer';
import {basePath} from '../../helpers/base-path';
import AutoFilter from '../parts/auto-filter';
import Options from '../parts/options';

interface IProps {
    isReady?: boolean;
    visible?: boolean;
    hardVisible?: boolean;
}

class Results extends React.Component<IProps, any> {

    public render() {
        const {isReady, visible, hardVisible} = this.props;

        const msg = [];
        /*if (hardVisible) {
         msg.push(<div key={msg.length} className="alert alert-warning">
         Výsledková listina je určená pouze pro organizátory!!!</div> );
         }*/
        /* if (!this.state.isOrg) {
         msg.push(
         <div key={msg.length} className="alert alert-info">
         Na výsledkovou listinu se díváte jako "Public"</div>
         );
         }*/

        if (!isReady) {
            return (
                <div className="load" style={{textAlign:'center',}}>
                    <img src={basePath+'/images/gears.svg'} style={{width:'50%'}}/>
                </div>)
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
        isReady: state.options.isReady,
        hardVisible: state.options.hardVisible,
        visible: state.timer.visible,
    }
};

export default connect(mapStateToProps, null)(Results);

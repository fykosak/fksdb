import * as React from 'react';
import {connect} from 'react-redux';

import Results from './containers/results';
import Statistics from './containers/statistics';
import {basePath} from '../helpers/base-path';

interface IProps {
    page?: string;
    isReady?: boolean;
}

class FyziklaniDashboard extends React.Component<IProps,void> {

    public render() {
        const {isReady, page} = this.props;

        if (!isReady) {
            return (
                <div className="load" style={{textAlign:'center',}}>
                    <img src={basePath+'/images/gears.svg'} style={{width:'50%'}}/>
                </div>)
        }
        switch (page) {
            case 'stats':
                return (<Statistics/>);
            default :
                return (<Results/>);
        }
    };
}

const mapStateToProps = (state, ownProps): IProps => {
    return {
        ...ownProps,
        isReady: state.options.isReady,
        page: state.options.page,
    }
};

export default connect(
    mapStateToProps,
    null,
)(FyziklaniDashboard);

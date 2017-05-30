import * as React from 'react';
import {connect} from 'react-redux';

import Results from './containers/results';

interface IProps {
    page?: string;
}

class FyziklaniDashboard extends React.Component<IProps,void> {

    public render() {
        const {page} = this.props;
        switch (page) {
            default :
                return (<Results/>);
        }
    };
}

const mapStateToProps = (state, ownProps): IProps => {
    return {
        ...ownProps,
        page: state.options.page,
    }
};

export default connect(
    mapStateToProps,
    null,
)(FyziklaniDashboard);

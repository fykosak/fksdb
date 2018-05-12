import * as React from 'react';
import { connect } from 'react-redux';
import Form from './form';

class Container extends React.Component<{}, {}> {
    public render() {
        return <><Form/></>;
    }
}

const mapStateToProps = (): {} => {
    return {};
};

export default connect(mapStateToProps, (): {} => {
    return {};
})(Container);

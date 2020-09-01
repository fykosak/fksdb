import { fetchSuccess } from '@fetchApi/actions';
import { Response2 } from '@fetchApi/interfaces';
import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';

interface OwnProps<D> {
    storeMap: Response2<D>;
    children: any;
}

interface DispatchProps<D> {
    onInit(data: Response2<D>): void;
}

class StoreLoader<D> extends React.Component<OwnProps<D> & DispatchProps<D>, {}> {
    public componentDidMount() {
        const {storeMap, onInit} = this.props;
        onInit(storeMap);
    }

    public render() {
        return <>
            {this.props.children}
        </>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps<any> => {
    return {
        onInit: (data) => dispatch(fetchSuccess(data)),
    };
};

export default connect(
    null,
    mapDispatchToProps,
)(StoreLoader);

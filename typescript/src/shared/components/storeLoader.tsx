import { fetchSuccess } from '@fetchApi/actions';
import { Response2 } from '@fetchApi/interfaces';
import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';

interface OwnProps<D> {
    storeMap: {
        [accessKey: string]: Response2<D>;
    };
    children: any;
}

interface DispatchProps<D> {
    onInit(data: Response2<D>, accessKey: string): void;
}

class StoreLoader<D> extends React.Component<OwnProps<D> & DispatchProps<D>, {}> {
    public componentDidMount() {
        const {storeMap, onInit} = this.props;
        for (const key in storeMap) {
            if (storeMap.hasOwnProperty(key)) {
                onInit(storeMap[key], key);
            }
        }
    }

    public render() {
        return <>
            {this.props.children}
        </>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps<any> => {
    return {
        onInit: (data, accessKey) => dispatch(fetchSuccess(data, accessKey)),
    };
};

export default connect(
    null,
    mapDispatchToProps,
)(StoreLoader);

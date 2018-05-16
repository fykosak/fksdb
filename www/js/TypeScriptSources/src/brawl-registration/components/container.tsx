import * as React from 'react';
import { connect } from 'react-redux';
import Form from './form';
import { IDefinitionsState } from '../reducers/definitions';
import { setDefinitions } from '../actions/definitions';

interface IState {
    onAddDefinitions?: (def: IDefinitionsState) => void;
}

interface IProps {
    definitions: IDefinitionsState;
}

class Container extends React.Component<IState & IProps, {}> {
    public componentDidMount() {
        this.props.onAddDefinitions(this.props.definitions);
    }

    public render() {
        return <><Form/></>;
    }
}

const mapStateToProps = (): {} => {
    return {};
};

export default connect(mapStateToProps, (dispatch): IState => {
    return {
        onAddDefinitions: (definitions: IDefinitionsState) => dispatch(setDefinitions(definitions)),
    };
})(Container);

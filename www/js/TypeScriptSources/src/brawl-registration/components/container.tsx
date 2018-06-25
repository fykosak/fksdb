import * as React from 'react';
import { connect } from 'react-redux';
import LangIndex from '../../lang/components/async';
import { setDefinitions } from '../../shared/definitions/actions/definitions';
import { IDefinitionsState } from '../../shared/definitions/interfaces';
import Form from './form';

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
        return <>
            <Form/>
            <LangIndex/>
        </>;
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

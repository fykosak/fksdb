import * as React from 'react';
import { connect } from 'react-redux';
import Powered from '../../../shared/powered';
import Loading from '../../helpers/components/loading';
import { IFyziklaniResultsStore } from '../reducers';
import HardVisibleSwitch from './hard-visible-switch/';
import Results from './results/';
import FilterSelect from './results/filter/select';

interface IState {
    isReady?: boolean;
    isOrg?: boolean;
}

interface IProps {
    accessKey: string;
    mode: string;
}

class App extends React.Component<IState & IProps, {}> {
    public render() {

        const {isReady, mode, accessKey, isOrg} = this.props;
        if (!isReady) {
            return <Loading/>;
        }
        return (<>
            {isOrg && <HardVisibleSwitch/>}
            <FilterSelect mode={mode}/>
            <Results accessKey={accessKey} basePath={'/'} mode={mode}/>
            <Powered/>
        </>);
    }
}

const mapStateToProps = (state: IFyziklaniResultsStore): IState => {
    return {
        isOrg: state.options.isOrg,
        isReady: state.options.isReady,
    };
};

export default connect(
    mapStateToProps,
    (): IState => {
        return {};
    },
)(App);

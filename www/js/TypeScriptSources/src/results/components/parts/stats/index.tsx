import * as React from 'react';
import { connect } from 'react-redux';
import Lang from '../../../../lang/components/lang';
import { IStore } from '../../../reducers/';
import Timer from '../timer';
import TasksStats from './task/index';
import TeamStats from './team/index';

interface IState {
    subPage?: string;
}

class Statistics extends React.Component<IState, {}> {

    public render() {
        let content = null;
        const { subPage } = this.props;
        switch (subPage) {
            case 'teams':
            default:
                content = (<TeamStats/>);
                break;
            case 'task':
                content = (<TasksStats/>);
        }
        return (
            <div className="container">
                <h1><Lang text={'statistics'}/></h1>

                {content}
                <Timer/>
            </div>
        );
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        subPage: state.options.subPage,
    };
};

export default connect(mapStateToProps, null)(Statistics);

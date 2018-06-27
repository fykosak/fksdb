import * as React from 'react';

import Timer from '../timer';
import TasksStats from './task/index';
import TeamStats from './team/index';

import { connect } from 'react-redux';
import { lang } from '../../../lang/index';
import { IStore } from '../../../reducers/index';

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
                <h1>{lang.getLang('statistics')}</h1>

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

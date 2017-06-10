import * as React from 'react';
import {connect} from 'react-redux';

import TasksStats from '../parts/stats/tasks-stats';
import TeamStats from '../parts/stats/team-stats';
import Navigation from '../parts/stats/navigation';

interface IProps {
    subPage: string;
}

class Statistics extends React.Component<IProps, void> {

    render() {
        let content = null;
        const {subPage} = this.props;
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
                <h1>Statistics of Physics Brawl</h1>
                <Navigation/>
                {content}
            </div>
        );
    }
}

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        subPage: state.options.subPage,
    };
};

export default connect(mapStateToProps, null)(Statistics);

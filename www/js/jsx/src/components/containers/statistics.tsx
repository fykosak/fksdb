import * as React from 'react';
import {connect} from 'react-redux';

import TasksStats from '../parts/tasks-stats';

class Statistics extends React.Component<any,void> {
    render() {
        // TODO jazyk
        return (<div className="container">
            <h1>Štatistky fyzikláni</h1>
            <h2>štatistika úloh</h2>
            <TasksStats/>

        </div>);
    }
}

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        teams: state.results.teams,
        tasks: state.results.tasks,
        submits: state.results.submits,
    }
};

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        ...ownProps,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Statistics);

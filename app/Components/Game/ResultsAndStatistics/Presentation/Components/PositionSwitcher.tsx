import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';
import { ACTION_SET_PARAMS, Params } from '../../actions/presentation';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface StateProps {
    categories: string[];
    category: string;
    cols: number;
    teams: TeamModel[];
    rows: number;
    delay: number;
    position: number;
}

interface DispatchProps {
    onSetParams(data: Params): void;
}

class PositionSwitcher extends React.Component<StateProps & DispatchProps, never> {
    private abortRun = false;

    public componentDidMount() {
        return this.run();
    }

    public render() {
        return null;
    }

    public componentWillUnmount() {
        this.abortRun = true;
    }

    private async run(): Promise<void> | never {

        const {cols, rows, position, delay, onSetParams, category, teams} = this.props;
        let activeTeams;
        if (category) {
            activeTeams = teams.filter((team) => {
                return team.category === category;
            });
        } else {
            activeTeams = teams;
        }
        let newPosition = position + (cols * rows);

        let newCategory = category;
        if (newPosition >= activeTeams.length) {
            newCategory = this.getCategory();
            newPosition = 0;
        }
        await new Promise<void>((resolve) => {
            setTimeout(() => {
                onSetParams({position: newPosition, category: newCategory});
                resolve();
            }, delay);
        });
        if (this.abortRun) {
            return;
        }
        await this.run();
    }

    private getCategory(): string {
        const {categories, category} = this.props;
        const index = categories.indexOf(category);
        if (index === -1) {
            return categories[0];
        }
        if (index === categories.length) {
            return null;
        }
        return categories[index + 1];
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onSetParams: (data) => dispatch({
            data,
            type: ACTION_SET_PARAMS,
        }),
    };
};
const mapStateToPros = (state: Store): StateProps => {
    return {
        categories: state.data.categories,
        category: state.presentation.category,
        cols: state.presentation.cols,
        delay: state.presentation.delay,
        position: state.presentation.position,
        rows: state.presentation.rows,
        teams: state.data.teams,
    };
};

export default connect(
    mapStateToPros,
    mapDispatchToProps,
)(PositionSwitcher);

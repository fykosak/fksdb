import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { Team } from '../../../../helpers/interfaces';
import { setPosition } from '../../../actions/presentation/setPosition';
import { FyziklaniResultsStore } from '../../../reducers';

interface State {
    categories?: string[];
    category?: string;
    cols?: number;
    teams?: Team[];
    rows?: number;
    delay?: number;
    position?: number;

    onSetNewPosition?(position: number, category?: string): void;
}

class PositionSwitcher extends React.Component<State, {}> {
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

    private async run(): Promise<void> {

        const {cols, rows, position, delay, onSetNewPosition, category, teams} = this.props;
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
                onSetNewPosition(newPosition, newCategory);
                resolve();
            }, delay);
        });
        if (this.abortRun) {
            return;
        }
        return this.run();
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

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): State => {
    return {
        onSetNewPosition: (position: number, category?: string) => dispatch(setPosition(position, category)),
    };
};
const mapStateToPros = (state: FyziklaniResultsStore): State => {
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

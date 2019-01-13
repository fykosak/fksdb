import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { ITeam } from '../../../../helpers/interfaces';
import { setPosition } from '../../../actions/Presentation/SetPosition';
import { IFyziklaniResultsStore } from '../../../reducers';

interface IState {
    cols?: number;
    teams?: ITeam[];
    rows?: number;
    delay?: number;
    position?: number;

    onSetNewPosition?(position: number): void;
}

class PositionSwitcher extends React.Component<IState, {}> {

    public componentDidMount() {
        return this.evalute();
    }

    public render() {
        return null;
    }

    private async evalute() {

        const {cols, rows, position, delay, onSetNewPosition, teams} = this.props;
        let newPosition = position + (cols * rows);
        if (newPosition > teams.length) {
            newPosition = 0;
        }
        await new Promise<void>((resolve) => {
            setTimeout(() => {
                onSetNewPosition(newPosition);
                resolve();
            }, delay);
        });
        return this.evalute();
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): IState => {
    return {
        onSetNewPosition: (position: number) => dispatch(setPosition(position)),
    };
};
const mapStateToPros = (state: IFyziklaniResultsStore): IState => {
    return {
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

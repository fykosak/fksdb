import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { ITeam } from '../../helpers/interfaces';
import { dropItem } from '../actions/dragndrop';
import { IStore } from '../reducers/';
import Team from './team';

interface IState {
    onDrop?: (teamId: number, place: any) => void;
    teams?: ITeam[];
    draggedTeamId?: number;
}

interface IProps {
    x: number;
    y: number;
    roomId: number;
}

class Place extends React.Component<IState & IProps, {}> {

    public render() {

        const { x, y, onDrop, teams, draggedTeamId, roomId } = this.props;
        const team = teams && teams.filter((currentTeam) => {
            return (currentTeam.x === x) && (currentTeam.y === y) && (currentTeam.roomId === roomId);
        })[0];
        return (<td
            onDragOver={(e) => {
                if (!team) {
                    e.preventDefault();
                }
            }}
            onClick={() => draggedTeamId ? onDrop(draggedTeamId, { x, y, roomId }) : null}
            onDrop={() => {
                onDrop(draggedTeamId, { x, y, roomId });
            }}>
            {team && <Team
                team={team}
            />}
        </td>);
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onDrop: (teamId, place) => dispatch(dropItem(teamId, place)),
    };
};

const mapStateToProps = (state: IStore): IState => {
    return {
        draggedTeamId: state.dragNDrop.draggedTeamId,
        teams: state.teams,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Place);

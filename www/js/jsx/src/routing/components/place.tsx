import * as React from 'react';
import Team from './team';

import {
    connect,
    Dispatch,
} from 'react-redux';
import { dropItem } from '../actions/dragndrop';
import { ITeam } from '../interfaces';
import { IStore } from '../reducers/index';

interface IState {
    onDrop?: (teamId: number, place: any) => void;
    teams?: ITeam[];
    draggedTeamId?: number;
}
interface IProps {
    x: number;
    y: number;
    room: string;
}
class Place extends React.Component<IState & IProps, {}> {

    public render() {

        const { x, y, onDrop, teams, draggedTeamId, room } = this.props;
        const team = teams && teams.filter((currentTeam) => {
                return (currentTeam.x === x) && (currentTeam.y === y) && (currentTeam.room === room);
            })[0];
        return (<td
            style={{ width: '5rem', border: 'solid 1px red', height: '5rem' }}
            onDragOver={(e) => {
                if (!team) {
                    e.preventDefault();
                }
            }
            }
            onDrop={() => {
                onDrop(draggedTeamId, { x, y, room });
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
        draggedTeamId: state.dragNDrop.draggedTeamID,
        teams: state.teams,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Place);

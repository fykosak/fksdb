import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import {
    IPlace,
    ITeam,
} from '../../../helpers/interfaces/';
import { dropItem } from '../../actions/dragndrop';
import { IRoutingDragNDropData } from '../../middleware/interfaces';
import { IFyziklaniRoutingStore } from '../../reducers/';
import Team from '../team/';

interface IState {
    onDrop?: (teamId: number, place: IPlace) => void;
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

        const {x, y, onDrop, teams, draggedTeamId, roomId} = this.props;
        const team = teams && teams.filter((currentTeam) => {
            return (currentTeam.x === x) && (currentTeam.y === y) && (currentTeam.roomId === roomId);
        })[0];
        return (<td
            onDragOver={(e) => {
                if (!team) {
                    e.preventDefault();
                }
            }}
            onClick={() => draggedTeamId ? onDrop(draggedTeamId, {x, y, roomId, room: null}) : null}
            onDrop={() => {
                onDrop(draggedTeamId, {x, y, roomId, room: null});
            }}>
            {team && <Team
                team={team}
            />}
        </td>);
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action>): IState => {
    return {
        onDrop: (teamId, place) => dispatch(dropItem<IRoutingDragNDropData>({teamId, place})),
    };
};

const mapStateToProps = (state: IFyziklaniRoutingStore): IState => {
    return {
        draggedTeamId: (state.dragNDrop.data && state.dragNDrop.data.hasOwnProperty('teamId')) ? state.dragNDrop.data.teamId : null,
        teams: state.teams.availableTeams,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Place);

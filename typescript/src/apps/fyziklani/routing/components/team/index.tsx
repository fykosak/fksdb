import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { Team } from '../../../helpers/interfaces/';
import {
    dragEnd,
    dragStart,
} from '../../actions/dragndrop';
import { removeTeamPlace } from '../../actions/teams';
import { DragNDropData } from '../../middleware/interfaces';
import { Store as RoutingStore } from '../../reducers/';

interface StateProps {
    isUpdated: boolean;
    isDragged: boolean;
}

interface DispatchProps {
    onDragStart(teamId: number): void;

    onDragEnd(): void;

    onRemovePlace(teamId: number): void;
}

interface OwnProps {
    team: Team;
}

class TeamComponent extends React.Component<OwnProps & StateProps & DispatchProps, {}> {
    public render() {

        const {onDragStart, onDragEnd, team, onRemovePlace, isUpdated, isDragged} = this.props;

        const hasPlace = (team.x !== null && team.y !== null && team.roomId !== null);

        return (
            <div className={'mb-3 ' + (hasPlace ? 'col-12' : 'col-6')}
                 draggable={true}
                 onDragStart={(event) => {
                     event.dataTransfer.setData('text/plain', '');
                     event.dataTransfer.dropEffect = 'copy';
                     onDragStart(team.teamId);
                 }}
                 onClick={() => isDragged ? onDragEnd() : onDragStart(team.teamId)}
                 onDragEnd={onDragEnd}
                 id={'team' + team.teamId}>
                <div className={'card ' + (isDragged ? 'text-white bg-primary' : '')}>
                    <div className="card-body card-block">
                        <h6 className="card-title fyziklani-headline-red">
                            {team.name}
                            {hasPlace && (
                                <button className="close" onClick={(event) => {
                                    event.stopPropagation();
                                    onRemovePlace(team.teamId);
                                }}>&times;</button>
                            )}</h6>
                        <span className={'badge badge-fyziklani'}>Category: {team.category}</span>
                        <small className="text-muted">{team.status}</small>
                        <p>
                            {isUpdated && (<span className="updated-confirm-text text-center">updated</span>)}
                        </p>
                    </div>
                </div>
            </div>);
    }
}

const mapStateToProps = (state: RoutingStore, ownProps: OwnProps): StateProps => {
    return {
        isDragged: state.dragNDrop.data && (state.dragNDrop.data.teamId === ownProps.team.teamId),
        isUpdated: (state.teams.updatedTeams.indexOf(ownProps.team.teamId) !== -1),
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onDragEnd: () => dispatch(dragEnd()),
        onDragStart: (teamId) => dispatch(dragStart<DragNDropData>({teamId})),
        onRemovePlace: (teamId) => dispatch(removeTeamPlace(teamId)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(TeamComponent);

import * as React from 'react';
import { connect } from 'react-redux';
import {
    dragEnd,
    dragStart,
} from '../actions/dragndrop';
import { removeTeamPlace } from '../actions/teams';
import { ITeam } from '../reducers/teams';

interface IState {
    onDragStart?: (teamID: number) => void;
    onDragEnd?: () => void;
    onRemovePlace?: (teamID: number) => void;
}
interface IProps {
    team: ITeam;
}
class Team extends React.Component<IProps & IState, {}> {
    public render() {

        const { onDragStart, onDragEnd, team, onRemovePlace } = this.props;
        let className = 'panel';
        const hasPlace = (team.x !== undefined && team.y !== undefined && team.room !== undefined);
        switch (team.category) {
            case 'A':
                className += ' panel-danger';
                break;
            case 'B':
                className += ' panel-warning';
                break;
            case 'C':
                className += ' panel-success';
                break;
            default:
        }
        return (
            <div className={hasPlace ? 'col-lg-12' : 'col-lg-6'}
                 draggable={ true }
                 onDragStart={() => onDragStart(team.teamID)}
                 onDragEnd={onDragEnd}>
                <div className={className}>
                    <div className="panel-heading">{team.name + ' (' + team.category + ')'}</div>
                    <div className="panel-body">
                        {hasPlace && (<button onClick={() => onRemovePlace(team.teamID)}>X</button>)}
                    </div>
                </div>
            </div>);
    }
}

const mapDispatchToProps = (dispatch): IState => {
    return {
        onDragEnd: () => dispatch(dragEnd()),
        onDragStart: (teamID) => dispatch(dragStart(teamID)),
        onRemovePlace: (teamId) => dispatch(removeTeamPlace(teamId)),
    };
};

export default connect(null, mapDispatchToProps)(Team);

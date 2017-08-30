import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import {
    dragEnd,
    dragStart,
} from '../actions/dragndrop';
import { removeTeamPlace } from '../actions/teams';
import { ITeam } from '../interfaces';
import { IStore } from '../reducers/index';

interface IState {
    isUpdated?: boolean;
    onDragStart?: (teamID: number) => void;
    onDragEnd?: () => void;
    onRemovePlace?: (teamID: number) => void;
}
interface IProps {
    team: ITeam;
}
class Team extends React.Component<IProps & IState, {}> {
    public render() {

        const { onDragStart, onDragEnd, team, onRemovePlace, isUpdated } = this.props;
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
                    <div className="panel-heading">
                        {team.name + ' (' + team.category + ')'}
                    </div>
                    <div className="panel-body">
                        <p>
                            {isUpdated && (<span className="updated-confirm-text text-center">updated</span>)}
                            {hasPlace && (
                                <button className="close" onClick={() => onRemovePlace(team.teamID)}>&times;</button>
                            )}
                        </p>
                    </div>
                </div>
            </div>);
    }
}

const mapStateToProps = (state: IStore, ownProps: IProps): IState => {

    return {
        isUpdated: (state.save.updatedTeams.indexOf(ownProps.team.teamID) !== -1),
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onDragEnd: () => dispatch(dragEnd()),
        onDragStart: (teamId) => dispatch(dragStart(teamId)),
        onRemovePlace: (teamId) => dispatch(removeTeamPlace(teamId)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Team);

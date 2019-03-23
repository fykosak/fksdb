import { Action } from 'redux';

export const ACTION_SET_ACTIVE_POINTS = '@@fyziklani/ACTION_SET_ACTIVE_POINTS';

export interface ActionSetActivePoints extends Action<string> {
    activePoints: number;
}

export const setActivePoints = (activePoints: number): ActionSetActivePoints => {
    return {
        activePoints,
        type: ACTION_SET_ACTIVE_POINTS,
    };
};

export interface ActionSetTeamId extends Action<string> {
    teamId: number;
}

export const ACTION_SET_TASK_ID = '@@fyziklani/ACTION_SET_TASK_ID';

export interface ActionSetTaskId extends Action<string> {
    taskId: number;
}

export const setTaskId = (taskId: number): ActionSetTaskId => {
    return {
        taskId,
        type: ACTION_SET_TASK_ID,
    };
};

export const ACTION_SET_FIRST_TEAM_ID = 'ACTION_SET_FIRST_TEAM_ID';

export const setFirstTeamId = (teamId: number): ActionSetTeamId => {
    return {
        teamId,
        type: ACTION_SET_FIRST_TEAM_ID,
    };
};

export const ACTION_SET_SECOND_TEAM_ID = 'ACTION_SET_SECOND_TEAM_ID';
export const setSecondTeamId = (teamId: number): ActionSetTeamId => {
    return {
        teamId,
        type: ACTION_SET_SECOND_TEAM_ID,
    };
};

export interface ActionSetAggregationTime extends Action<string> {
    time: number;
}

export const ACTION_SET_AGGREGATION_TIME = 'ACTION_SET_AGGREGATION_TIME';
export const setAggregationTime = (time: number): ActionSetAggregationTime => {
    return {
        time,
        type: ACTION_SET_AGGREGATION_TIME,
    };
};

export const ACTION_SET_FROM_DATE = 'ACTION_SET_FROM_DATE';

export interface ActionSetFromDate extends Action<string> {
    from: Date;
}

export const setFromDate = (from: Date): ActionSetFromDate => {
    return {
        from,
        type: ACTION_SET_FROM_DATE,
    };

};
export const ACTION_SET_TO_DATE = 'ACTION_SET_TO_DATE';

export interface ActionSetToDate extends Action<string> {
    to: Date;
}

export const setToDate = (to: Date): ActionSetToDate => {
    return {
        to,
        type: ACTION_SET_TO_DATE,
    };
};

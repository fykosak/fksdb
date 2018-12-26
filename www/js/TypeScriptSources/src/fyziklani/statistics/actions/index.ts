import { Action } from 'redux';

export const ACTION_SET_ACTIVE_POINTS = '@@fyziklani/ACTION_SET_ACTIVE_POINTS';

export interface IActionSetActivePoints extends Action<string> {
    activePoints: number;
}

export const setActivePoints = (activePoints: number): IActionSetActivePoints => {
    return {
        activePoints,
        type: ACTION_SET_ACTIVE_POINTS,
    };
};

export interface IActionSetTeamId extends Action<string> {
    teamId: number;
}

export const ACTION_SET_TASK_ID = '@@fyziklani/ACTION_SET_TASK_ID';

export interface IActionSetTaskId extends Action<string> {
    taskId: number;
}

export const setTaskId = (taskId: number): IActionSetTaskId => {
    return {
        taskId,
        type: ACTION_SET_TASK_ID,
    };
};

export const ACTION_SET_FIRST_TEAM_ID = 'ACTION_SET_FIRST_TEAM_ID';

export const setFirstTeamId = (teamId: number): IActionSetTeamId => {
    return {
        teamId,
        type: ACTION_SET_FIRST_TEAM_ID,
    };
};

export const ACTION_SET_SECOND_TEAM_ID = 'ACTION_SET_SECOND_TEAM_ID';
export const setSecondTeamId = (teamId: number): IActionSetTeamId => {
    return {
        teamId,
        type: ACTION_SET_SECOND_TEAM_ID,
    };
};

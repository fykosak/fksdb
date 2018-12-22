import { Action } from 'redux';

export const ACTION_SET_ACTIVE_POINTS = '@@fyziklani/ACTION_SET_ACTIVE_POINTS';

export interface IActionSetActivePoints extends Action {
    activePoints: number;
}

export const setActivePoints = (activePoints: number): IActionSetActivePoints => {
    return {
        activePoints,
        type: ACTION_SET_ACTIVE_POINTS,
    };
};

export const ACTION_SET_TEAM_ID = '@@fyziklani/ACTION_SET_TEAM_ID';

export interface IActionSetTeamId extends Action {
    teamId: number;
}

export const setTeamId = (teamId: number): IActionSetTeamId => {
    return {
        teamId,
        type: ACTION_SET_TEAM_ID,
    };
};

export const ACTION_SET_TASK_ID = '@@fyziklani/ACTION_SET_TASK_ID';

export interface IActionSetTaskId extends Action {
    taskId: number;
}

export const setTaskId = (taskId: number): IActionSetTaskId => {
    return {
        taskId,
        type: ACTION_SET_TASK_ID,
    };
};

export const ACTION_SET_CHART_TYPE = '@@fyziklani/ACTION_SET_CHART_TYPE';

export interface IActionSetChartType extends Action {
    chartType: string;
}

export const setChartType = (chartType: string): IActionSetChartType => {
    return {
        chartType,
        type: ACTION_SET_CHART_TYPE,
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

import * as React from 'react';
import { Item } from '../../Helpers/calculate-data';

interface OwnProps<AvailablePoints extends number> {
    item: Item<AvailablePoints>;
    position: number;
    availablePoints: Array<AvailablePoints>;
}

export default function TeamRow<AvailablePoints extends number>(
    {
        item,
        position,
        availablePoints,
    }: OwnProps<AvailablePoints>,
) {
    const average = item.count > 0 ? Math.round(item.points / item.count * 100) / 100 : '-';
    return <div className="row team-row" key={item.team.teamId}>
        <div className="col-1">{position}</div>
        <div className="col-1">{item.team.category}</div>
        <div className="col-4 team-name-col">{item.team.name}</div>
        <div className="col-1">{item.points}</div>
        <div className="col-1">{item.count}</div>
        <div className="col-1">{average}</div>
        <div className="col-3 team-progress">
            <div className="progress">
                {availablePoints.map((points) => {
                    const width = ((item.groups[points]) ?
                        (Math.round(item.groups[points] / item.count * 100)) :
                        0) + '%';
                    return <div
                        key={points.toString()}
                        className="progress-bar"
                        data-points={points}
                        style={{width}}
                    />;
                })}
            </div>
        </div>
    </div>;
}

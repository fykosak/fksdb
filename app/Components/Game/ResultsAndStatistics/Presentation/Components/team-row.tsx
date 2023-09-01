import * as React from 'react';
import { Item } from '../../Helpers/calculate-data';

interface OwnProps {
    item: Item;
    position: number;
    availablePoints: number[];
}

export default function TeamRow({item, position, availablePoints}: OwnProps) {

    const average = item.count > 0 ? Math.round(item.points / item.count * 100) / 100 : '-';
    return <div className="row team-row" key={item.team.teamId}>
        <div className="col-1">{position}</div>
        <div className="col-1">{item.team.category}</div>
        <div className="col-4 team-name-col">{item.team.name}</div>
        <div className="col-1">{item.points}</div>
        <div className="col-1">{item.count}</div>
        <div className="col-1">{average}</div>
        <div className="col-3">
            <div className="progress">
                {availablePoints.map((points) => {
                    const width = ((item.groups[points]) ?
                        (Math.round(item.groups[points] / item.count * 100)) :
                        0) + '%';
                    return <div
                        key={points}
                        className="progress-bar"
                        data-points={points}
                        style={{width}}
                    />;
                })}
            </div>
        </div>
    </div>;
}

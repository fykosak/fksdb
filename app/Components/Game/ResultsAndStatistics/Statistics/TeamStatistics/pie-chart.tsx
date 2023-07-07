import { arc, pie, PieArcDatum } from 'd3-shape';
import { SubmitModel } from 'FKSDB/Models/ORM/Models/Fyziklani/submit-model';
import * as React from 'react';
import { useSelector } from 'react-redux';
import './pie-chart.scss';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface OwnProps {
    teamId: number;
}

interface PointGroupItem {
    points: number;
    count: number;
}

export default function PieChart({teamId}: OwnProps) {

    const submits = useSelector((state: Store) => state.data.submits);
    const pointsCategories: { [key: number]: PointGroupItem } = {
        1: {points: 1, count: 0},
        2: {points: 2, count: 0},
        3: {points: 3, count: 0},
        5: {points: 5, count: 0},
    };

    let totalSubmits = 0;
    for (const index in submits) {
        if (Object.hasOwn(submits, index)) {
            const submit: SubmitModel = submits[index];
            const {teamId: submitTeamId, points} = submit;
            if (teamId === submitTeamId) {

                if (points !== null && points !== 0) {
                    if (Object.hasOwn(pointsCategories, points)) {
                        totalSubmits++;
                        pointsCategories[points].count++;
                    }
                }
            }
        }
    }
    const arcEl = arc<PieArcDatum<PointGroupItem>>().innerRadius(0).outerRadius(150);
    const filteredData: PointGroupItem[] = [];
    for (const points in pointsCategories) {
        if (Object.hasOwn(pointsCategories, points)) {
            const item = pointsCategories[points];
            if (item.count !== 0) {
                filteredData.push(item);
            }
        }
    }

    const pie = getPieData<PointGroupItem>(filteredData);
    // TODO types
    const paths = pie.map((item: PieArcDatum<PointGroupItem>, index: number) => {
        return <path
            className="arc"
            d={arcEl(item)}
            key={index}
            style={{'--arc-color': 'var(--color-fof-points-' + item.data.points + ')'} as React.CSSProperties}
        />;
    });

    const labels = pie.map((item: PieArcDatum<PointGroupItem>, index: number) => {
        return <g key={index}>
            <text transform={'translate(' + arcEl.centroid(item).toString() + ')'}>
                <tspan>{Math.floor(item.data.count * 100 / totalSubmits)}%</tspan>
            </text>
        </g>;
    });
    return <div className="chart-game-team-pie">
        <svg viewBox="0 0 400 400" className="chart">
            <g transform="translate(200,200)">
                {paths}
                {labels}
            </g>
        </svg>
    </div>;
}

const getPieData = <Datum extends { count: number }>(data: Datum[]): Array<PieArcDatum<Datum>> => {
    return pie<Datum>().value((item: Datum) => {
        return +item.count;
    })(data);
}

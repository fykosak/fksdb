import * as React from 'react';

interface OwnProps {
    order: number;
}

export default function Ordinal({order}: OwnProps) {
    let sup = 'th';
    switch (order) {
        case 1:
            sup = 'st';
            break;
        case 2:
            sup = 'nd';
            break;
        case 3:
            sup = 'rd';
    }
    return <>{order}<sup>{sup}</sup></>;
}

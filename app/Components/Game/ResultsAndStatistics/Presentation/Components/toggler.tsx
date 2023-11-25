import Images from 'FKSDB/Components/Game/ResultsAndStatistics/Presentation/Components/Timer/images';
import Timer from 'FKSDB/Components/Game/ResultsAndStatistics/Presentation/Components/Timer/timer';
import * as React from 'react';
import { useSelector } from 'react-redux';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface OwnProps {
    children: React.ReactNode;
    event: 'fof' | 'ctyrboj';
}

export default function Toggler({children, event}: OwnProps) {
    const hardVisible = useSelector((state: Store) => state.presentation.hardVisible);
    const visible = useSelector((state: Store) => state.timer.visible);
    if (visible || hardVisible) {
        return <>
            <Timer mode="small"/>
            {children}
        </>;
    }
    return <div className="h-100 d-flex flex-column justify-content-around align-items-center">
        <img className="w-50 logo" alt="" src={
            event === 'fof'
                ? '/images/fyziklani/fyziklani_2023_logo.png'
                : '/images/logo/vedecky_ctyrboj.png'
        }/>
        <Images/>
        <Timer mode="big"/>
        <img className="logo-sponsors" alt="" src={
            event === 'fof'
                ? '/images/fyziklani/fyziklani_2023_sponsors.svg' : ''
        }/>
    </div>;
}

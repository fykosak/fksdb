import * as React from 'react';
import FyziklaniDashboard from './fyziklani-dashboard';
import Downloader from './helpers/downloader';
import Clock from './helpers/clock';
import BackLink from './parts/nav-bar/back-link';

export default class FyziklaniApp extends React.Component<any, any> {
    public render() {
        return (
            <div>
                <Downloader/>
                <FyziklaniDashboard/>
            </div>
        );
    }
}

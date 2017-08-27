import * as React from 'react';
import BrawlDashboard from './brawl-dashboard';
import Downloader from './helpers/downloader';

export default class BrawlApp extends React.Component<{}, {}> {
    public render() {
        return (
            <div>
                <Downloader/>
                <BrawlDashboard/>
            </div>
        );
    }
}

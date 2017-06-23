import * as React from 'react';
import FyziklaniDashboard from './fyziklani-dashboard';
import Downloader from './helpers/downloader';

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

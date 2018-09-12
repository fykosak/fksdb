import * as React from 'react';
import { lang } from '../../../../i18n/i18n';
import Timer from '../../../helpers/components/timer/';
import TasksStats from './task/index';
import TeamStats from './team/index';

interface IProps {
    mode: string;
}

export default class Statistics extends React.Component<IProps, {}> {

    public render() {
        let content = null;
        const {mode} = this.props;
        switch (mode) {
            case 'teams':
            default:
                content = (<TeamStats/>);
                break;
            case 'task':
                content = (<TasksStats/>);
        }
        return (
            <div className="container">
                {content}
                <Timer/>
            </div>
        );
    }
}

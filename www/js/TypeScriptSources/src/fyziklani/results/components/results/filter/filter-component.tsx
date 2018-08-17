import * as React from 'react';
import { Filter } from './filter';

interface IProps {
    filter: Filter;
    onClick?: (filter: Filter) => void;
    onCloseClick?: (filter: Filter) => void;
    active?: boolean;
}

export default class FilterComponent extends React.Component<IProps, {}> {

    public render() {
        const {active, onClick, onCloseClick, filter} = this.props;

        return <span
            className={'btn ' + (active ? 'btn-primary' : 'btn-secondary')}
            onClick={() => {
                if (onClick) {
                    onClick(filter);
                }
            }}
        >{filter.getHeadline()}
            {onCloseClick && (<span className="ml-3" onClick={() => {
                onCloseClick(filter);
            }}>&times;</span>)}
            </span>;
    }
}

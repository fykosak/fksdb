import * as React from 'react';
import { Filter } from '../filter';

interface OwnProps {
    active: boolean;
    filter: Filter;
    type?: 'primary' | 'danger' | 'warning';

    onClick?(filter: Filter): void;

    onCloseClick?(filter: Filter): void;

}

export default class FilterComponent extends React.Component<OwnProps> {

    public render() {
        const {active, onClick, onCloseClick, filter, type} = this.props;

        return <a
            href="#"
            className={'badge ms-3 ' + (active ? 'bg-success' : ('bg-' + (type ? type : 'secondary')))}
            onClick={() => {
                if (onClick) {
                    onClick(filter);
                }
            }}
        >{filter.getHeadline()}
            {onCloseClick && (<span className="ms-3" onClick={() => {
                onCloseClick(filter);
            }}>&times;</span>)}
        </a>;
    }
}

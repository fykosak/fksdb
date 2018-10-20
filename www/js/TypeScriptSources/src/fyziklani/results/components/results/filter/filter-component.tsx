import * as React from 'react';
import { Filter } from './filter';

interface IProps {
    active: boolean;
    filter: Filter;
    type?: 'primary' | 'danger' | 'warning';

    onClick?(filter: Filter): void;

    onCloseClick?(filter: Filter): void;

}

export default class FilterComponent extends React.Component<IProps, {}> {

    public render() {
        const {active, onClick, onCloseClick, filter, type} = this.props;

        return <a
            href="#"
            className={'badge ml-3 ' + (active ? 'badge-success' : ('badge-' + (type ? type : 'secondary')))}
            onClick={() => {
                if (onClick) {
                    onClick(filter);
                }
            }}
        >{filter.getHeadline()}
            {onCloseClick && (<span className="ml-3" onClick={() => {
                onCloseClick(filter);
            }}>&times;</span>)}
        </a>;
    }
}

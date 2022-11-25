import * as React from 'react';
import { connect } from 'react-redux';
import { createFilters } from '../filter';
import FilterComponent from './Filter';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface StateProps {
    categories: string[];
}

class FilterSelect extends React.Component<StateProps> {

    public render() {
        const {categories} = this.props;
        return <>
            {createFilters(categories).map((availableFilter, key) => {
                return <FilterComponent
                    key={key}
                    filter={availableFilter}
                />;
            })}
        </>;
    }
}

const mapStateToPros = (state: Store): StateProps => {
    return {
        categories: state.data.categories,
    };
};

export default connect(
    mapStateToPros,
    null,
)(FilterSelect);

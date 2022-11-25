import * as React from 'react';
import { Filter } from '../filter';
import { Action, Dispatch } from 'redux';
import { setFilter } from '../../actions/table';
import { connect } from 'react-redux';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface OwnProps {
    filter: Filter;
}

interface StateProps {
    active: boolean;
    categories: string[];
}

interface DispatchProps {
    onSetFilter(filter: Filter | null): void;
}


class FilterComponent extends React.Component<OwnProps & StateProps & DispatchProps> {

    public render() {
        const {active, filter, onSetFilter} = this.props;
        return <a
            href="#"
            className={'btn ms-3 ' + (active ? 'btn-outline-success' : 'btn-outline-secondary')}
            onClick={() => {
                onSetFilter(active ? null : filter);
            }}
        >{filter.getHeadline()}</a>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onSetFilter: (filter: Filter) => dispatch(setFilter(filter)),
    };
};
const mapStateToPros = (state: Store, ownProps: OwnProps): StateProps => {
    return {
        categories: state.data.categories,
        active: ownProps.filter.same(state.tableFilter.filter),
    };
};

export default connect(
    mapStateToPros,
    mapDispatchToProps,
)(FilterComponent);

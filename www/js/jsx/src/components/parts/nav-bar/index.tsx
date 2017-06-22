import * as React from 'react';
import {connect} from 'react-redux';
import {changePage} from '../../../actions/options';
import BackLink from './back-link';

interface IProps {
    page?: string;
    onchangePage?: Function;
}

class NavBar extends React.Component<IProps, void> {

    public render() {
        const {page, onchangePage} = this.props;
        const pageNames = [
            {id: 'table', name: 'Table'},
            {id: 'stats', name: 'Statistics'},
        ];
        const pages = pageNames.map((item) => {
            return (<li className={'nav-item ' + ((page === item.id) ? 'active' : '')}>
                <a className="nav-link"
                   href="#"
                   onClick={() => onchangePage(item.id)}
                >{item.name}</a>
            </li>);
        });
        return (
            <nav className="navbar sticky-top navbar-inverse bg-primary navbar-toggleable-sm mb-3 row">
                <button className="navbar-toggler navbar-toggler-right"
                        type="button" data-toggle="collapse"
                        data-target="#navbar">
                    <span className="navbar-toggler-icon"/>
                </button>
                <a className="navbar-brand" href="#">Physics Brawl</a>
                <div className="collapse navbar-collapse" id="navbar">
                    <ul className="navbar-nav">
                        {pages}
                    </ul>
                    <ul className="navbar-nav ml-auto">
                        <li className="navbar-item">
                            <BackLink/>
                        </li>
                    </ul>
                </div>
            </nav>
        );
    };
}

const mapStateToProps = (state, ownProps): IProps => {
    return {
        ...ownProps,
        page: state.options.page,
    };
};

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        ...ownProps,
        onchangePage: (page) => dispatch(changePage(page)),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(NavBar);


/*public componentDidMount() {

    this.applyNextAutoFilter(0);
}

private applyNextAutoFilter(i) {
    $("html, body").scrollTop();

    let t = 15000;
    let {autoSwitch, autoDisplayCategory, autoDisplayRoom} = this.state;
    if (autoSwitch) {
        switch (i) {
            case 0: {
                t = 30000;
                this.setState({displayCategory: null, displayRoom: null});
                break;
            }
            case 1: {
                if (autoDisplayRoom) {
                    this.setState({displayCategory: autoDisplayCategory});
                } else {
                    t = 0;
                }
                break;
            }
            case 2: {
                if (autoDisplayCategory) {
                    this.setState({displayRoom: autoDisplayRoom});
                } else {
                    t = 0;
                }
                break;
            }
        }
        if (t > 1000) {
            $("html, body").delay(t / 3).animate({scrollTop: $(document).height()}, t / 3);
        }
    }
    setTimeout(() => {
        i++;
        i = i % 3;
        this.applyNextAutoFilter(i);
    }, t);
};
*/
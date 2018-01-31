const autoClose = async function () {
    const element = document.getElementById('auto-close');
    debugger;
    if (!element) {
        return;
    }
    const delay = async function (time) {
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve()
            }, time);
        });
    };
    for (let i = 10; i > 0; i--) {
        element.innerText = 'Stránka sa zavrie za ' + i + ' sekund';
        await delay(1000);
        if (i === 1) {
            window.close();
        }
    }
};

$(() => autoClose());

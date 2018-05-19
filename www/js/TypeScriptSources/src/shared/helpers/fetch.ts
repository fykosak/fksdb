export async function netteFetch<F, D>(data: F = null, success: (data: D) => void, error: (e) => void): Promise<D> {
    const netteJQuery: any = $;
    return new Promise((resolve: (d: D) => void, reject) => {
        netteJQuery.nette.ajax({
            data,
            error: (e) => {
                error(e);
                reject(e);
            },
            method: 'POST',
            success: (d: D) => {
                success(d);
                resolve(d);
            },
        });
    });
}

export async function uploadFile<F, D>(data: F = null, success: (data: D) => void, error: (e: any) => void): Promise<D> {
    return new Promise((resolve: (d: D) => void, reject) => {
        $.ajax({
            cache: false,
            contentType: false,
            data,
            dataType: 'json',
            error: (e) => {
                reject(e);
                error(e);
            },
            processData: false,
            success: (d: D) => {
                resolve(d);
                success(d);
            },
            type: 'POST',
            url: '#',
        });
    });
}

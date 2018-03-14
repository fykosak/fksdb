export async function netteFetch(data: any = null, success: (data: any) => void, error: (e) => void): Promise<any> {
    const netteJQuery: any = $;
    return new Promise((resolve, reject) => {
        netteJQuery.nette.ajax({
            data,
            method: 'POST',
            error: (e) => {
                error(e);
                reject(e);
            },
            success: (d) => {
                success(d);
                resolve(d);
            },
        });
    });
}

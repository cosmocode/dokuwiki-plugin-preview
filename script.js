class PreviewPlugin {

    #preview = null;
    #abortController = null;

    constructor() {
        this.#preview = document.createElement('div');
        this.#preview.classList.add('plugin-preview');
        this.#preview.style.position = 'absolute';
        this.#preview.style.top = '0';
        this.#preview.style.left = '0';
        this.#preview.style.display = 'none';
        document.body.append(this.#preview);
    }

    attach() {
        const selector = JSINFO.plugin.preview.selector;
        const links = document.querySelectorAll(selector + ' a.wikilink1');
        links.forEach(link => {
            link.addEventListener('mouseenter', this.onMouseEnter.bind(this));
            link.addEventListener('mouseleave', this.onMouseLeave.bind(this));
            link.removeAttribute('title');
        });
    }

    async loadPreview(id) {
        try {
            if (this.#abortController !== null) this.#abortController.abort();
            this.#abortController = new AbortController();

            const data = await fetch(
                DOKU_BASE + 'lib/exe/ajax.php?call=plugin_preview&id=' + encodeURIComponent(id),
                {
                    signal: this.#abortController.signal,
                    method: 'POST',
                }
            );
            if (data.ok) {
                this.#preview.innerHTML = await data.text();
                this.#preview.style.display = 'block';
            }
        } catch (ignored) {
            // we don't care about errors
        }
    }

    async onMouseEnter(e) {
        this.#preview.style.top = e.pageY + 10 + 'px';
        this.#preview.style.left = e.pageX + 10 + 'px';
        await this.loadPreview(e.target.dataset.wikiId);
    }

    onMouseLeave(e) {
        this.#preview.style.display = 'none';
        if (this.#abortController !== null) this.#abortController.abort();
        this.#abortController = null;
    }

}


document.addEventListener('DOMContentLoaded', () => {
    const preview = new PreviewPlugin();
    preview.attach();
});
